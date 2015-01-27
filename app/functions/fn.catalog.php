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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\ProductTabs;
use Tygh\Navigation\LastView;
use Tygh\Languages\Languages;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// ------------------------- 'Products' object functions ------------------------------------

/**
 * Gets full product data by its id
 *
 * @param int $product_id Product ID
 * @param mixed $auth Array with authorization data
 * @param string $lang_code 2 letters language code
 * @param string $field_list List of fields for retrieving
 * @param boolean $get_add_pairs Get additional images
 * @param boolean $get_main_pair Get main images
 * @param boolean $get_taxes Get taxes
 * @param boolean $get_qty_discounts Get quantity discounts
 * @param boolean $preview Is product previewed by admin
 * @param boolean $features Get product features
 * @param boolean $skip_company_condition Skip company condition and retrieve product data for displayin on other store page. (Works only in ULT)
 * @return mixed Array with product data
 */
function fn_get_product_data($product_id, &$auth, $lang_code = CART_LANGUAGE, $field_list = '', $get_add_pairs = true, $get_main_pair = true, $get_taxes = true, $get_qty_discounts = false, $preview = false, $features = true, $skip_company_condition = false, $feature_variants_selected_only = false)
{
    $product_id = intval($product_id);

    /**
     * Change parameters for getting product data
     *
     * @param int     $product_id             Product ID
     * @param mixed   $auth                   Array with authorization data
     * @param string  $lang_code              2 letters language code
     * @param string  $field_list             List of fields for retrieving
     * @param boolean $get_add_pairs          Get additional images
     * @param boolean $get_main_pair          Get main images
     * @param boolean $get_taxes              Get taxes
     * @param boolean $get_qty_discounts      Get quantity discounts
     * @param boolean $preview                Is product previewed by admin
     * @param boolean $features               Get product features
     * @param boolean $skip_company_condition Skip company condition and retrieve product data for displaying on other store page. (Works only in ULT)
     */
    fn_set_hook('get_product_data_pre', $product_id, $auth, $lang_code, $field_list, $get_add_pairs, $get_main_pair, $get_taxes, $get_qty_discounts, $preview, $features, $skip_company_condition);

    $usergroup_ids = !empty($auth['usergroup_ids']) ? $auth['usergroup_ids'] : array();

    if (!empty($product_id)) {

        if (empty($field_list)) {
            $descriptions_list = "?:product_descriptions.*";
            $field_list = "?:products.*, $descriptions_list";
        }
        $field_list .= ", MIN(IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, ?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100)) as price";
        $field_list .= ", GROUP_CONCAT(IF(?:products_categories.link_type = 'M', CONCAT(?:products_categories.category_id, 'M'), ?:products_categories.category_id)) as category_ids";
        $field_list .= ", popularity.total as popularity";

        $price_usergroup = db_quote(" AND ?:product_prices.usergroup_id IN (?n)", ((AREA == 'A' && !defined('ORDER_MANAGEMENT')) ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $usergroup_ids)));

        $_p_statuses = array('A', 'H');
        $_c_statuses = array('A', 'H');

        $condition = $join = $avail_cond = '';

        if (!fn_allowed_for('ULTIMATE')) {
            $avail_cond .= fn_get_company_condition('?:products.company_id');
        } else {
            if (!$skip_company_condition && Registry::get('runtime.company_id')) {
                if (AREA == 'C') {
                    $avail_cond .= fn_get_company_condition('?:categories.company_id');
                } else {
                    $avail_cond .= ' AND (' . fn_get_company_condition('?:categories.company_id', false) . ' OR ' . fn_get_company_condition('?:products.company_id', false) . ')';
                }
            }

            if (Registry::get('runtime.company_id')) {
                $field_list .= ', IF('
                        . 'shared_prices.product_id IS NOT NULL,'
                        . 'MIN(IF(shared_prices.percentage_discount = 0, shared_prices.price, shared_prices.price - (shared_prices.price * shared_prices.percentage_discount)/100)),'
                        . 'MIN(IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, ?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100))'
                    . ') as price'
                ;
                $shared_prices_usergroup = db_quote(" AND shared_prices.usergroup_id IN (?n)", ((AREA == 'A' && !defined('ORDER_MANAGEMENT')) ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $usergroup_ids)));
                $join .= db_quote(' LEFT JOIN ?:ult_product_prices shared_prices ON shared_prices.product_id = ?:products.product_id AND shared_prices.company_id = ?i AND shared_prices.lower_limit = 1 ?p', Registry::get('runtime.company_id'), $shared_prices_usergroup);
            }
        }

        $avail_cond .= (AREA == 'C' && empty($preview)) ? ' AND (' . fn_find_array_in_set($usergroup_ids, "?:categories.usergroup_ids", true) . ')' : '';
        $avail_cond .= (AREA == 'C' && empty($preview)) ? ' AND (' . fn_find_array_in_set($usergroup_ids, "?:products.usergroup_ids", true) . ')' : '';
        $avail_cond .= (AREA == 'C' && empty($preview)) ? db_quote(' AND ?:categories.status IN (?a) AND ?:products.status IN (?a)', $_c_statuses, $_p_statuses) : '';

        $avail_cond .= fn_get_localizations_condition('?:products.localization');
        $avail_cond .= fn_get_localizations_condition('?:categories.localization');

        if (AREA == 'C' && !$preview) {
            $field_list .= ', companies.company as company_name';
            $condition .= " AND (companies.status = 'A' OR ?:products.company_id = 0) ";
            $join .= " LEFT JOIN ?:companies as companies ON companies.company_id = ?:products.company_id";
        }

        $join .= " INNER JOIN ?:products_categories ON ?:products_categories.product_id = ?:products.product_id INNER JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id $avail_cond";
        $join .= " LEFT JOIN ?:product_popularity as popularity ON popularity.product_id = ?:products.product_id";

        /**
         * Change SQL parameters for product data select
         *
         * @param int $product_id Product ID
         * @param string $field_list List of fields for retrieving
         * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
         * @param mixed $auth Array with authorization data
         * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
         * @param string $condition Condition for selecting product data
         */
        fn_set_hook('get_product_data', $product_id, $field_list, $join, $auth, $lang_code, $condition);

        $product_data = db_get_row("SELECT $field_list FROM ?:products LEFT JOIN ?:product_prices ON ?:product_prices.product_id = ?:products.product_id AND ?:product_prices.lower_limit = 1 ?p LEFT JOIN ?:product_descriptions ON ?:product_descriptions.product_id = ?:products.product_id AND ?:product_descriptions.lang_code = ?s ?p WHERE ?:products.product_id = ?i ?p GROUP BY ?:products.product_id", $price_usergroup, $lang_code, $join, $product_id, $condition);

        if (empty($product_data)) {
            return false;
        }

        $product_data['base_price'] = $product_data['price']; // save base price (without discounts, etc...)

        list($product_data['category_ids'], $product_data['main_category']) = fn_convert_categories($product_data['category_ids']);

        // Generate meta description automatically
        if (!empty($product_data['full_description']) && empty($product_data['meta_description']) && defined('AUTO_META_DESCRIPTION') && AREA != 'A') {
            $product_data['meta_description'] = fn_generate_meta_description($product_data['full_description']);
        }

        // If tracking with options is enabled, check if at least one combination has positive amount
        if (!empty($product_data['tracking']) && $product_data['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
            $product_data['amount'] = db_get_field("SELECT MAX(amount) FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
        }

        $product_data['product_id'] = $product_id;

        // Get product shipping settings
        if (!empty($product_data['shipping_params'])) {
            $product_data = array_merge(unserialize($product_data['shipping_params']), $product_data);
        }

        // Get main image pair
        if ($get_main_pair == true) {
            $product_data['main_pair'] = fn_get_image_pairs($product_id, 'product', 'M', true, true, $lang_code);
        }

        // Get additional image pairs
        if ($get_add_pairs == true) {
            $product_data['image_pairs'] = fn_get_image_pairs($product_id, 'product', 'A', true, true, $lang_code);
        }

        // Get taxes
        $product_data['tax_ids'] = !empty($product_data['tax_ids']) ? explode(',', $product_data['tax_ids']) : array();

        // Get qty discounts
        if ($get_qty_discounts == true) {
            fn_get_product_prices($product_id, $product_data, $auth);
        }

        if ($features) {
            // Get product features

            $path = !empty($product_data['main_category']) ? explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $product_data['main_category'])) : '';

            if (fn_allowed_for('ULTIMATE')) {
                $product_data['shared_product'] = fn_ult_is_shared_product($product_id);
                if ($product_data['shared_product'] == 'Y') {
                    //we should get features for all categories, not only main
                    $path = !empty($product_data['category_ids']) ? explode('/', implode('/', db_get_fields("SELECT id_path FROM ?:categories WHERE category_id IN (?a)", $product_data['category_ids']))) : '';
                }
            }

            $_params = array(
                'category_ids' => $path,
                'product_id' => $product_id,
                'product_company_id' => !empty($product_data['company_id']) ? $product_data['company_id'] : 0,
                'statuses' => AREA == 'C' ? array('A') : array('A', 'H'),
                'variants' => true,
                'plain' => false,
                'display_on' => AREA == 'A' ? '' : 'product',
                'existent_only' => (AREA != 'A'),
                'variants_selected_only' => $feature_variants_selected_only
            );
            list($product_data['product_features']) = fn_get_product_features($_params, 0, $lang_code);
            $product_data['header_features'] = fn_get_product_features_list($product_data, 'H');
        } else {
            $product_data['product_features'] = fn_get_product_features_list($product_data, 'A');
        }

    } else {
        return false;
    }

    $product_data['detailed_params']['info_type'] = 'D';

    /**
     * Particularize product data
     *
     * @param array   $product_data List with product fields
     * @param mixed   $auth         Array with authorization data
     * @param boolean $preview      Is product previewed by admin
     * @param string  $lang_code    2-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_product_data_post', $product_data, $auth, $preview, $lang_code);

    return (!empty($product_data) ? $product_data : false);
}

/**
 * Gets product name by id
 *
 * @param mixed $product_id Integer product id, or array of product ids
 * @param string $lang_code 2-letter language code
 * @param boolean $as_array Flag: if set, result will be returned as array <i>(product_id => product)</i>; otherwise only product name will be returned
 * @return mixed In case 1 <i>product_id</i> is passed and <i>as_array</i> is not set, a product name string is returned;
 * Array <i>(product_id => product)</i> for all given <i>product_ids</i>;
 * <i>False</i> if <i>$product_id</i> is not defined
 */
function fn_get_product_name($product_id, $lang_code = CART_LANGUAGE, $as_array = false)
{
    /**
     * Change parameters for getting product name
     *
     * @param int/array $product_id Product integer identifier
     * @param string    $lang_code  Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param boolean   $as_array   Flag determines if even one product name should be returned as array
     */
    fn_set_hook('get_product_name_pre', $product_id, $lang_code, $as_array);

    $result = false;
    if (!empty($product_id)) {
        if (!is_array($product_id) && strpos($product_id, ',') !== false) {
            $product_id = explode(',', $product_id);
        }

        $field_list = 'pd.product_id as product_id, pd.product as product';
        $join = '';
        if (is_array($product_id) || $as_array == true) {
            $condition = db_quote(' AND pd.product_id IN (?n) AND pd.lang_code = ?s', $product_id, $lang_code);
        } else {
            $condition = db_quote(' AND pd.product_id = ?i AND pd.lang_code = ?s', $product_id, $lang_code);
        }

        /**
        * Change SQL parameters for getting product name
        *
        * @param int/array $product_id Product integer identifier
        * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
        * @param boolean $as_array Flag determines if even one product name should be returned as array
        * @param string $field_list List of fields for retrieving
        * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
        * @param string $condition Condition for selecting product name
        */
        fn_set_hook('get_product_name', $product_id, $lang_code, $as_array, $field_list, $join, $condition);

        $result = db_get_hash_single_array("SELECT $field_list FROM ?:product_descriptions pd $join WHERE 1 $condition", array('product_id', 'product'));
        if (!(is_array($product_id) || $as_array == true)) {
            if (isset($result[$product_id])) {
                $result = $result[$product_id];
            } else {
                $result = null;
            }
        }
    }

    /**
     * Change product name selected by $product_id & $lang_code params
     *
     * @param int/array    $product_id Product integer identifier
     * @param string       $lang_code  Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param boolean      $as_array   Flag determines if even one product name should be returned as array
     * @param string/array $result     String containig product name or array with products names depending on $product_id param
     */
    fn_set_hook('get_product_name_post', $product_id, $lang_code, $as_array, $result);

    return $result;
}

/**
 * Gets product price by id
 *
 * @param int $product_id Product id
 * @param int $amount Optional parameter: necessary to calculate quantity discounts
 * @param array $auth Array of authorization data
 * @return float Price
 */
function fn_get_product_price($product_id, $amount, &$auth)
{
    /**
     * Change parameters for getting product price
     *
     * @param int   $product_id Product identifier
     * @param int   $amount     Amount of products, required to get wholesale price
     * @param array $auth       Array of user authentication data (e.g. uid, usergroup_ids, etc.)
     */
    fn_set_hook('get_product_price_pre', $product_id, $amount, $auth);

    $usergroup_condition = db_quote("AND ?:product_prices.usergroup_id IN (?n)", ((AREA == 'C' || defined('ORDER_MANAGEMENT')) ? array_merge(array(USERGROUP_ALL), $auth['usergroup_ids']) : USERGROUP_ALL));

    $price = db_get_field(
        "SELECT MIN(IF(?:product_prices.percentage_discount = 0, ?:product_prices.price, "
            . "?:product_prices.price - (?:product_prices.price * ?:product_prices.percentage_discount)/100)) as price "
        . "FROM ?:product_prices "
        . "WHERE lower_limit <=?i AND ?:product_prices.product_id = ?i ?p "
        . "ORDER BY lower_limit DESC LIMIT 1",
        $amount, $product_id, $usergroup_condition
    );

    /**
     * Change product price
     *
     * @param int   $product_id Product identifier
     * @param int   $amount     Amount of products, required to get wholesale price
     * @param array $auth       Array of user authentication data (e.g. uid, usergroup_ids, etc.)
     * @param float $price
     */
    fn_set_hook('get_product_price_post', $product_id, $amount, $auth, $price);

    return (empty($price))? 0 : floatval($price);
}

/**
 * Gets product descriptions to the given language
 *
 * @param array $products Array of products
 * @param string $fields List of fields to be translated
 * @param string $lang_code 2-letter language code.
 * @param boolean $translate_options Flag: if set, product options are also translated; otherwise not
 */
function fn_translate_products(&$products, $fields = '',$lang_code = '', $translate_options = false)
{
    /**
     * Change parameters for translating product text data
     *
     * @param array  $products          List of products
     * @param string $fields            Fields of products that should be translated
     * @param string $lang_code         Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param bool   $translate_options Flag that defines whether we want to translate product options. Set it to "true" in case you want.
     */
    fn_set_hook('translate_products_pre', $products, $fields, $lang_code, $translate_options);

    if (empty($fields)) {
        $fields = 'product, short_description, full_description';
    }

    foreach ($products as $k => $v) {
        if (!empty($v['deleted_product'])) {
            continue;
        }
        $descriptions = db_get_row("SELECT $fields FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $v['product_id'], $lang_code);
        foreach ($descriptions as $k1 => $v1) {
            $products[$k][$k1] = $v1;
        }
        if ($translate_options && !empty($v['product_options'])) {
            foreach ($v['product_options'] as $k1 => $v1) {
                $option_descriptions = db_get_row("SELECT option_name, option_text, description, comment FROM ?:product_options_descriptions WHERE option_id = ?i AND lang_code = ?s", $v1['option_id'], $lang_code);
                foreach ($option_descriptions as $k2 => $v2) {
                    $products[$k]['product_options'][$k1][$k2] = $v2;
                }

                if ($v1['option_type'] == 'C') {
                    $products[$k]['product_options'][$k1]['variant_name'] = (empty($v1['position'])) ? __('no', '', $lang_code) : __('yes', '', $lang_code);
                } elseif ($v1['option_type'] == 'S' || $v1['option_type'] == 'R') {
                    $variant_description = db_get_field("SELECT variant_name FROM ?:product_option_variants_descriptions WHERE variant_id = ?i AND lang_code = ?s", $v1['value'], $lang_code);
                    $products[$k]['product_options'][$k1]['variant_name'] = $variant_description;
                }
            }
        }
    }

    /**
     * Change translated products data
     *
     * @param array  $products          List of products
     * @param string $fields            Fields of products that should be translated
     * @param string $lang_code         Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param bool   $translate_options Flag that defines whether we want to translate product options. Set it to "true" in case you want.
     */
    fn_set_hook('translate_products_post', $products, $fields, $lang_code, $translate_options);
}

/**
 * Gets additional products data
 *
 * @param array $products Array with products
 * @param array $params Array of flags which determines which data should be gathered
 * @return array Array of products with additional information
 */
function fn_gather_additional_products_data(&$products, $params)
{
    /**
     * Change parameters for gathering additional products data
     *
     * @param array $products List of products
     * @param array $params   Parameters for gathering data
     */
    fn_set_hook('gather_additional_products_data_pre', $products, $params);

    if (empty($products)) {
        return;
    }

    // Set default values to input params
    $default_params = array (
        'get_icon' => false,
        'get_detailed' => false,
        'get_additional' => false,
        'get_options' => true,
        'get_discounts' => true,
        'get_features' => false,
        'get_extra' => false,
        'get_taxed_prices' => true,
        'get_for_one_product' => (!is_array(reset($products)))? true : false,
        'detailed_params' => true,
        'features_display_on' => 'C'
    );

    $params = array_merge($default_params, $params);

    $auth = & $_SESSION['auth'];
    $allow_negative_amount = Registry::get('settings.General.allow_negative_amount');
    $inventory_tracking = Registry::get('settings.General.inventory_tracking');

    if ($params['get_for_one_product']) {
        $products = array($products);
    }

    $product_ids = array();
    foreach ($products as $v) {
            $product_ids[] = $v['product_id'];
    }

    if ($params['get_icon'] || $params['get_detailed']) {
        $products_images = fn_get_image_pairs($product_ids, 'product', 'M', $params['get_icon'], $params['get_detailed'], CART_LANGUAGE);
    }

    if ($params['get_additional']) {
        $additional_images = fn_get_image_pairs($product_ids, 'product', 'A', true, true, CART_LANGUAGE);
    }

    if ($params['get_options']) {
        $product_options = fn_get_product_options($product_ids, CART_LANGUAGE);
    } else {
        $has_product_options = db_get_hash_array("SELECT a.option_id, a.product_id FROM ?:product_options AS a WHERE a.product_id IN (?n) AND a.status = 'A'", 'product_id', $product_ids);
        $has_product_options_links = db_get_hash_array("SELECT c.option_id, c.product_id FROM ?:product_global_option_links AS c LEFT JOIN ?:product_options AS a ON a.option_id = c.option_id WHERE a.status = 'A' AND c.product_id IN (?n)", 'product_id', $product_ids);
    }

    /**
     * Changes before gathering additional products data
     *
     * @param array $product_ids               Array of product identifiers
     * @param array $params                    Parameteres for gathering data
     * @param array $products                  Array of products
     * @param mixed $auth                      Array of user authentication data
     * @param array $products_images           Array with product main images
     * @param array $additional_images         Array with product additional images
     * @param array $product_options           Array with product options
     * @param array $has_product_options       Array of flags determines if product has options
     * @param array $has_product_options_links Array of flags determines if product has option links
     */
    fn_set_hook('gather_additional_products_data_params', $product_ids, $params, $products, $auth, $products_images, $additional_images, $product_options, $has_product_options, $has_product_options_links);

    // foreach $products
    foreach ($products as &$_product) {
        $product = $_product;
        $product_id = $product['product_id'];

        // Get images
        if ($params['get_icon'] == true || $params['get_detailed'] == true) {
            if (empty($product['main_pair']) && !empty($products_images[$product_id])) {
                $product['main_pair'] = reset($products_images[$product_id]);
            }
        }

        if ($params['get_additional'] == true) {
            if (empty($product['image_pairs']) && !empty($additional_images[$product_id])) {
                $product['image_pairs'] = $additional_images[$product_id];
            }
        }

        if (!isset($product['base_price'])) {
            $product['base_price'] = $product['price']; // save base price (without discounts, etc...)
        }

        /**
         * Changes before gathering product options
         *
         * @param array $product Product data
         * @param mixed $auth Array of user authentication data
         * @param array $params Parameteres for gathering data
         */
        fn_set_hook('gather_additional_product_data_before_options', $product, $auth, $params);

        // Convert product categories
        if (!empty($product['category_ids']) && !is_array($product['category_ids'])) {
            list($product['category_ids'], $product['main_category']) = fn_convert_categories($product['category_ids']);
        }

        $product['selected_options'] = empty($product['selected_options']) ? array() : $product['selected_options'];

        // Get product options
        if ($params['get_options'] && !empty($product_options[$product['product_id']])) {
            if (!isset($product['options_type']) || !isset($product['exceptions_type'])) {
                $types = db_get_row('SELECT options_type, exceptions_type FROM ?:products WHERE product_id = ?i', $product['product_id']);
                $product['options_type'] = $types['options_type'];
                $product['exceptions_type'] = $types['exceptions_type'];
            }

            if (empty($product['product_options'])) {
                if (!empty($product['combination'])) {
                    $selected_options = fn_get_product_options_by_combination($product['combination']);
                }

                $product['product_options'] = (!empty($selected_options)) ? fn_get_selected_product_options($product['product_id'], $selected_options, CART_LANGUAGE) : $product_options[$product_id];
            }

            $product = fn_apply_options_rules($product);

            if (!empty($params['get_icon']) || !empty($params['get_detailed'])) {
                // Get product options images
                if (!empty($product['combination_hash']) && !empty($product['product_options'])) {
                    $image = fn_get_image_pairs($product['combination_hash'], 'product_option', 'M', $params['get_icon'], $params['get_detailed'], CART_LANGUAGE);
                    if (!empty($image)) {
                        $product['main_pair'] = $image;
                    }
                }
            }
            $product['has_options'] = !empty($product['product_options']);

            if (!fn_allowed_for('ULTIMATE:FREE')) {
                $product = fn_apply_exceptions_rules($product);
            }

            // Change price
            $selected_options = isset($product['selected_options']) ? $product['selected_options'] : array();
            foreach ($product['product_options'] as $option) {
                if (!empty($option['disabled'])) {
                    unset($selected_options[$option['option_id']]);
                }
            }

            $product['selected_options'] = $selected_options;
            if (empty($product['modifiers_price'])) {
                $product['base_modifier'] = fn_apply_options_modifiers($selected_options, $product['base_price'], 'P', array(), array('product_data' => $product));
                $old_price = $product['price'];
                $product['price'] = fn_apply_options_modifiers($selected_options, $product['price'], 'P', array(), array('product_data' => $product));

                if (empty($product['original_price'])) {
                    $product['original_price'] = $old_price;
                }

                $product['original_price'] = fn_apply_options_modifiers($selected_options, $product['original_price'], 'P', array(), array('product_data' => $product));
                $product['modifiers_price'] = $product['price'] - $old_price;
            }

            if (!empty($product['list_price'])) {
                $product['list_price'] = fn_apply_options_modifiers($selected_options, $product['list_price'], 'P', array(), array('product_data' => $product));
            }

            if (!empty($product['prices']) && is_array($product['prices'])) {
                foreach ($product['prices'] as $pr_k => $pr_v) {
                    $product['prices'][$pr_k]['price'] = fn_apply_options_modifiers($selected_options, $pr_v['price'], 'P', array(), array('product_data' => $product));
                }
            }
        } else {
            $product['has_options'] = (!empty($has_product_options[$product_id]) || !empty($has_product_options_links[$product_id]))? true : false;
            $product['product_options'] = empty($product['product_options']) ? array() : $product['product_options'];
        }

        unset($selected_options);

        /**
         * Changes before gathering product discounts
         *
         * @param array $product Product data
         * @param mixed $auth Array of user authentication data
         * @param array $params Parameteres for gathering data
         */
        fn_set_hook('gather_additional_product_data_before_discounts', $product, $auth, $params);

        // Get product discounts
        if ($params['get_discounts'] && !isset($product['exclude_from_calculate'])) {
            fn_promotion_apply('catalog', $product, $auth);
            if (!empty($product['prices']) && is_array($product['prices'])) {
                $product_copy = $product;
                foreach ($product['prices'] as $pr_k => $pr_v) {
                    $product_copy['base_price'] = $product_copy['price'] = $pr_v['price'];
                    fn_promotion_apply('catalog', $product_copy, $auth);
                    $product['prices'][$pr_k]['price'] = $product_copy['price'];
                }
            }

            if (empty($product['discount']) && !empty($product['list_price']) && !empty($product['price']) && floatval($product['price']) && $product['list_price'] > $product['price']) {
                $product['list_discount'] = fn_format_price($product['list_price'] - $product['price']);
                $product['list_discount_prc'] = sprintf('%d', round($product['list_discount'] * 100 / $product['list_price']));
            }
        }

        // FIXME: old product options scheme
        $product['discounts'] = array('A' => 0, 'P' => 0);
        if (!empty($product['promotions'])) {
            foreach ($product['promotions'] as $v) {
                foreach ($v['bonuses'] as $a) {
                    if ($a['discount_bonus'] == 'to_fixed') {
                        $product['discounts']['A'] += $a['discount'];
                    } elseif ($a['discount_bonus'] == 'by_fixed') {
                        $product['discounts']['A'] += $a['discount_value'];
                    } elseif ($a['discount_bonus'] == 'to_percentage') {
                        $product['discounts']['P'] += 100 - $a['discount_value'];
                    } elseif ($a['discount_bonus'] == 'by_percentage') {
                        $product['discounts']['P'] += $a['discount_value'];
                    }
                }
            }
        }

        // Add product prices with taxes and without taxes
        if ($params['get_taxed_prices'] && AREA != 'A' && Registry::get('settings.Appearance.show_prices_taxed_clean') == 'Y' && $auth['tax_exempt'] != 'Y') {
            fn_get_taxed_and_clean_prices($product, $auth);
        }

        if ($params['get_features'] && !isset($product['product_features'])) {
            $product['product_features'] = fn_get_product_features_list($product, $params['features_display_on']);
        }

        if ($params['get_extra'] && !empty($product['is_edp']) && $product['is_edp'] == 'Y') {
            $product['agreement'] = array(fn_get_edp_agreements($product['product_id']));
        }

        $product['qty_content'] = fn_get_product_qty_content($product, $allow_negative_amount, $inventory_tracking);

        if ($params['detailed_params']) {
            $product['detailed_params'] = empty($product['detailed_params']) ? $params : array_merge($product['detailed_params'], $params);
        }

        /**
         * Add additional data to product
         *
         * @param array $product Product data
         * @param mixed $auth Array of user authentication data
         * @param array $params Parameteres for gathering data
         */
        fn_set_hook('gather_additional_product_data_post', $product, $auth, $params);
        $_product = $product;
    }// \foreach $products

    /**
     * Add additional data to products after gathering additional products data
     *
     * @param array $product_ids Array of product identifiers
     * @param array $params      Parameteres for gathering data
     * @param array $products    Array of products
     * @param mixed $auth        Array of user authentication data
     */
    fn_set_hook('gather_additional_products_data_post', $product_ids, $params, $products, $auth);

    if ($params['get_for_one_product'] == true) {
        $products = array_shift($products);
    }
}

/**
 * Forms a drop-down list of possible product quantity values with the given quantity step
 *
 * @param array $product Product data
 * @param char $allow_negative_amount Flag: allow or disallow negative product quantity(Y - allow, N - disallow)
 * @param char $inventory_tracking Flag: track product qiantity or not (Y - track, N - do not track)
 * @return array qty_content List of available quantity values with the given step
 */
function fn_get_product_qty_content($product, $allow_negative_amount, $inventory_tracking)
{
    $qty_content = array();

    if (!empty($product['qty_step'])) {

        $default_list_qty_count = 100;

        if (empty($product['min_qty'])) {
            $min_qty = $product['qty_step'];
        } else {
            $min_qty = fn_ceil_to_step($product['min_qty'], $product['qty_step']);
        }

        if (!empty($product['list_qty_count'])) {
            $max_list_qty = $product['list_qty_count'] * $product['qty_step'] + $min_qty - $product['qty_step'];
        } else {
            $max_list_qty = $default_list_qty_count * $product['qty_step'] + $min_qty - $product['qty_step'];
        }

        // max amount
        if ($product['tracking'] != ProductTracking::DO_NOT_TRACK && $allow_negative_amount != 'Y' && $inventory_tracking == 'Y') {
            if (isset($product['in_stock'])) {
                $max_qty =  fn_floor_to_step($product['in_stock'],  $product['qty_step']);

            } elseif (isset($product['inventory_amount'])) {
                $max_qty =  fn_floor_to_step($product['inventory_amount'],  $product['qty_step']);

            } elseif ($product['amount'] < $product['qty_step']) {
                $max_qty = $product['qty_step'];

            } else {
                $max_qty = fn_floor_to_step($product['amount'],  $product['qty_step']);
            }

            if (!empty($product['list_qty_count'])) {
                $max_qty = min($max_qty, $max_list_qty);
            }

        } else {
            $max_qty = $max_list_qty;
        }

        if (!empty($product['max_qty'])) {
            $max_qty = min($max_qty, fn_floor_to_step($product['max_qty'], $product['qty_step']));
        }

        for ($qty = $min_qty; $qty <= $max_qty; $qty += $product['qty_step']) {
            $qty_content[] = $qty;
        }
    }

    return $qty_content;
}

/**
 * Gets additional data for a single product
 *
 * @param array $product Product data
 * @param boolean $get_icon Flag that define if product icon should be gathered
 * @param boolean $get_detailed Flag determines if detailed image should be gathered
 * @param boolean $get_options Flag that define if product options should be gathered
 * @param boolean $get_discounts Flag that define if product discounts should be gathered
 * @param boolean $get_features Flag that define if product features should be gathered
 * @return array Product data with the additional information
 */
function fn_gather_additional_product_data(&$product, $get_icon = false, $get_detailed = false, $get_options = true, $get_discounts = true, $get_features = false)
{
    // Get specific settings
    $params = array(
        'get_icon' => $get_icon,
        'get_detailed' => $get_detailed,
        'get_options' => $get_options,
        'get_discounts' => $get_discounts,
        'get_features' => $get_features,
    );

    /**
     * Change parameters for gathering additional data for a product
     *
     * @param array $product Product data
     * @param array $params  parameters for gathering data
     */
    fn_set_hook('gather_additional_product_data_params', $product, $params);

    fn_gather_additional_products_data($product, $params);
}

/**
 * Returns product folders
 *
 * @param array $params
 *        int product_id     - ID of product
 *        string folder_ids  - get folders by ids
 *        string order_by
 * @return array folders, params
 */
function fn_get_product_file_folders($params, $lang_code = DESCR_SL)
{
    $params['product_id'] = !empty($params['product_id'])? $params['product_id'] : 0;
    $fields = array(
        'SUM(?:product_files.file_size) as folder_size',
        '?:product_file_folders.*',
        '?:product_file_folder_descriptions.folder_name'
    );
    $default_params = array(
        'product_id' => 0,
        'folder_ids' => '',
        'order_by' => 'position, folder_name',
    );
    $params = array_merge($default_params, $params);

    $join = db_quote(" LEFT JOIN ?:product_files ON ?:product_file_folders.folder_id = ?:product_files.folder_id LEFT JOIN ?:product_file_folder_descriptions ON ?:product_file_folder_descriptions.folder_id = ?:product_file_folders.folder_id AND ?:product_file_folder_descriptions.lang_code = ?s", $lang_code);
    $order = $params['order_by'];

    if (!empty($params['folder_ids'])) {
        $condition = db_quote("WHERE ?:product_file_folders.folder_id IN (?n)", $params['folder_ids']);
    } else {
        $condition = db_quote("WHERE ?:product_file_folders.product_id = ?i", $params['product_id']);
    }

    if (AREA == 'C') {
        $condition .= " AND ?:product_file_folders.status = 'A'";
    }

    $folders = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:product_file_folders ?p ?p GROUP BY folder_id ORDER BY ?p", $join, $condition, $order);

    return array($folders, $params);
}

/**
 * Returns product files
 * @param array $params
 *        int product_id     - ID of product
 *        bool preview_check - get files only with preview
 *        int order_id       - get order ekeys for the files
 *        string file_ids    - get files by ids
 * @return array files, params
 */
function fn_get_product_files($params, $lang_code = DESCR_SL)
{
    $default_params = array (
        'product_id'    => 0,
        'preview_check' => false,
        'order_id'      => 0,
        'file_ids'      => '',
    );
    $params = array_merge($default_params, $params);

    /**
     * Change parameters for getting product files
     *
     * @param array  $params
     * @param string $lang_code 2-letters language code
     */
    fn_set_hook('get_product_files_pre', $params, $lang_code);
    $fields = array(
        '?:product_files.*',
        '?:product_file_descriptions.file_name',
        '?:product_file_descriptions.license',
        '?:product_file_descriptions.readme'
    );

    $join = db_quote(" LEFT JOIN ?:product_file_descriptions ON ?:product_file_descriptions.file_id = ?:product_files.file_id AND ?:product_file_descriptions.lang_code = ?s", $lang_code);

    if (!empty($params['order_id'])) {
        $fields[] = '?:product_file_ekeys.active';
        $fields[] = '?:product_file_ekeys.downloads';
        $fields[] = '?:product_file_ekeys.ekey';

        $join .= db_quote(" LEFT JOIN ?:product_file_ekeys ON ?:product_file_ekeys.file_id = ?:product_files.file_id AND ?:product_file_ekeys.order_id = ?i", $params['order_id']);
        $join .= (AREA == 'C') ? " AND ?:product_file_ekeys.active = 'Y'" : '';
    }

    if (!empty($params['file_ids'])) {
        $condition = db_quote("WHERE ?:product_files.file_id IN (?n)", $params['file_ids']);
    } else {
        $condition = db_quote("WHERE ?:product_files.product_id = ?i", $params['product_id']);
    }

    if ($params['preview_check'] == true) {
        $condition .= " AND preview_path != ''";
    }

    if (AREA == 'C') {
        $condition .= " AND ?:product_files.status = 'A'";
    }

    /**
     * Change SQL parameters for product files selection
     *
     * @param array  $params
     * @param array  $fields    List of fields for retrieving
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     */
    fn_set_hook('get_product_files_before_select', $params, $fields, $join, $condition);

    $files = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:product_files ?p ?p ORDER BY position, file_name", $join, $condition);

    if (!empty($files)) {
        foreach ($files as $k => $file) {
            if (!empty($file['license']) && $file['agreement'] == 'Y') {
                $files[$k]['agreements'] = array($file);
            }
            if (!empty($file['product_id']) && !empty($file['ekey'])) {
                $files[$k]['edp_info'] = fn_get_product_edp_info($file['product_id'], $file['ekey']);
            }
        }
    }

    /**
     * Change product files
     *
     * @param array $params
     * @param array $files  Product files
     */
    fn_set_hook('get_product_files_post', $params, $files);

    return array($files, $params);
}

/**
 * Returns product folders and files merged and presented as a tree
 *
 * @param array  $folders Product folders
 * @param array  $files Product files
 * @return array tree
 */
function fn_build_files_tree($folders, $files)
{
    $tree = array();
    $folders = !empty($folders)? $folders : array();
    $files = !empty($files)? $files : array();

    if (is_array($folders) && is_array($files)) {

        foreach ($folders as $v_folder) {
            $subfiles = array();
            foreach ($files as $v_file) {
                if ($v_file['folder_id'] == $v_folder['folder_id']) {
                    $subfiles[] = $v_file;
                }
            }

            $v_folder['files'] = $subfiles;
            $tree['folders'][] = $v_folder;
        }

        foreach ($files as $v_file) {
            if (empty($v_file['folder_id'])) {
                $tree['files'][] = $v_file;
            }
        }

    }

    return $tree;
}

/**
 * Returns EDP ekey info
 *
 * @param int $product_id Product identifier
 * @param string $ekey Download key
 * @return array Download key info
 */
function fn_get_product_edp_info($product_id, $ekey)
{
    /**
     * Prepare params before getting EDP information
     *
     * @param int    $product_id Product identifier
     * @param string $ekey       Download key
     */
    fn_set_hook('get_product_edp_info_pre', $product_id, $ekey);

    $unlimited = db_get_field("SELECT unlimited_download FROM ?:products WHERE product_id = ?i", $product_id);
    $ttl_condition = ($unlimited == 'Y') ? '' :  db_quote(" AND ttl > ?i", TIME);

    $edp_info = db_get_row(
        "SELECT product_id, order_id, file_id "
            . "FROM ?:product_file_ekeys "
        . "WHERE product_id = ?i AND active = 'Y' AND ekey = ?s ?p",
        $product_id, $ekey, $ttl_condition
    );

    /**
     * Change product edp info
     *
     * @param array  $edp_info   EDP information
     * @param int    $product_id Product identifier
     * @param string $ekey       Download key
     */
    fn_set_hook('get_product_edp_info_post', $product_id, $ekey, $edp_info);

    return $edp_info;
}

/**
 * Gets EDP agreemetns
 *
 * @param int $product_id Product identifier
 * @param bool $file_name If true get file name in info, false otherwise
 * @return array EDP agreements data
 */
function fn_get_edp_agreements($product_id, $file_name = false)
{
    /**
     * Actions before getting edp agreements
     *
      * @param int $product_id Product identifier
      * @param bool $file_name Get file name
     */
    fn_set_hook('get_edp_agreements_pre', $product_id, $file_name);

    $join = '';
    $fields = array(
        '?:product_files.file_id',
        '?:product_files.agreement',
        '?:product_file_descriptions.license'
    );

    if ($file_name == true) {
        $join .= db_quote(" LEFT JOIN ?:product_file_descriptions ON ?:product_file_descriptions.file_id = ?:product_files.file_id AND product_file_descriptions.lang_code = ?s", CART_LANGUAGE);
        $fields[] = '?:product_file_descriptions.file_name';
    }

    /**
     * Prepare params before getting edp agreements
     *
     * @param int    $product_id Product identifier
     * @param string $join       Query join; it is treated as a JOIN clause
     * @param array  $fields     Array of table column names to be returned
     */
    fn_set_hook('get_edp_agreements_before_get_agriments', $product_id, $fields, $join);

    $edp_agreements = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:product_files INNER JOIN ?:product_file_descriptions ON ?:product_file_descriptions.file_id = ?:product_files.file_id AND ?:product_file_descriptions.lang_code = ?s WHERE ?:product_files.product_id = ?i AND ?:product_file_descriptions.license != '' AND ?:product_files.agreement = 'Y'", CART_LANGUAGE, $product_id);

    /**
     * Actions after getting edp agreements
     *
     * @param int   $product_id     Product identifier
     * @param bool  $file_name      If true get file name in info, false otherwise
     * @param array $edp_agreements EDP agreements data
     */
    fn_set_hook('get_edp_agreements_post', $product_id, $file_name, $edp_agreements);

    return $edp_agreements;
}

//-------------------------------------- 'Categories' object functions -----------------------------

/**
 * Gets subcategories list for current category (first-level categories only)
 *
 * @param int $category_id Category identifier
 * @param string $lang_code 2-letters language code
 * @return array Subcategories
 */
function fn_get_subcategories($category_id = '0', $lang_code = CART_LANGUAGE)
{
    $params = array (
        'category_id' => $category_id,
        'visible' => true,
        'get_images' => true,
    );

    /**
     * Change params before subcategories select
     *
     * @param int    $category_id Category identifier
     * @param int    $params      Params of subcategories search
     * @param string $lang_code   2-letters language code
     */
    fn_set_hook('get_subcategories_params', $category_id, $lang_code, $params);

    list($categories, ) = fn_get_categories($params, $lang_code);

    /**
     * Change subcategories
     *
     * @param int    $params     Params of subcategories search
     * @param string $lang_code  2-letters language code
     * @param array  $categories Subcategories
     */
    fn_set_hook('get_subcategories_post', $params, $lang_code, $categories);

    return $categories;
}

/**
 * Gets categories tree (multidimensional) from the current category
 *
 * @param int $category_id Category identifier
 * @param boolean $simple Flag that defines if category names path and product count should not be gathered
 * @param string $lang_code 2-letters language code
 * @return array Array of subcategories as a hierarchical tree
 */
function fn_get_categories_tree($category_id = '0', $simple = true, $lang_code = CART_LANGUAGE)
{
    $params = array (
        'category_id' => $category_id,
        'simple' => $simple
    );

    /**
     * Change params before categories tree select
     *
     * @param int     $category_id Category identifier
     * @param boolean $simple      Flag that defines if category names path and product count should not be gathered
     * @param string  $lang_code   2-letters language code
     * @param int     $params      Params of subcategories search
     */
    fn_set_hook('get_categories_tree_params', $category_id, $simple, $lang_code, $params);

    list($categories, ) = fn_get_categories($params, $lang_code);

    /**
     * Change categories tree
     *
     * @param int    $params     Params of subcategories search
     * @param string $lang_code  2-letters language code
     * @param array  $categories Categories tree
     */
    fn_set_hook('get_categories_tree_post', $params, $lang_code, $categories);

    return $categories;
}

/**
 * Gets categories tree (plain) from the current category
 *
 * @param int $category_id Category identifier
 * @param boolean $simple Flag that defines if category names path and product count should not be gathered
 * @param string $lang_code 2-letters language code
 * @param array $company_ids Identifiers of companies for that categories should be gathered
 * @return array Array of subategories as a simple list
 */
function fn_get_plain_categories_tree($category_id = '0', $simple = true, $lang_code = CART_LANGUAGE, $company_ids = '')
{
    $params = array (
        'category_id' => $category_id,
        'simple' => $simple,
        'visible' => false,
        'plain' => true,
        'company_ids' => $company_ids,
    );

    /**
     * Change params before plain categories tree select
     *
     * @param int     $category_id Category identifier
     * @param boolean $simple      Flag that defines if category names path and product count should not be gathered
     * @param string  $lang_code   2-letters language code
     * @param array   $company_ids Identifiers of companies for that categories should be gathered
     * @param int     $params      Params of subcategories search
     */
    fn_set_hook('get_plain_categories_tree_params', $category_id, $simple, $lang_code, $company_ids, $params);

    list($categories, ) = fn_get_categories($params, $lang_code);

    /**
     * Change categories tree
     *
     * @param int    $params     Params of subcategories search
     * @param string $lang_code  2-letters language code
     * @param array  $categories Categories tree
     */
    fn_set_hook('get_plain_categories_tree_post', $params, $lang_code, $categories);

    return $categories;
}

/**
 * Categories sorting function, compares two categories
 *
 * @param array $a First category data
 * @param array $b Second category data
 * @return int Result of comparison categories positions or categories names( if both categories positions are empty)
 */
function fn_cat_sort($a, $b)
{
    /**
     * Changes categories data before the comparison
     *
     * @param array $a First category data
     * @param array $b Second category data
     */
    fn_set_hook('cat_sort_pre', $a, $b);

    $result = 0;

    if (empty($a["position"]) && empty($b['position'])) {
        $result = strnatcmp($a["category"], $b["category"]);
    } else {
        $result = strnatcmp($a["position"], $b["position"]);
    }

    /**
     * Changes the result of categories comparison
     *
     * @param array $a      First category data
     * @param array $b      Second category data
     * @param int   $result Result of comparison categories positions or categories names( if both categories positions are empty)
     */
    fn_set_hook('cat_sort_post', $a, $b, $result);

    return $result;
}

/**
 * Checks if objects should be displayed in a picker
 *
 * @param string $table Name of SQL table with objects
 * @param int $threshold Value of the threshold after which the picker should be displayed
 * @return boolean Flag that defines if picker should be displayed
 */
function fn_show_picker($table, $threshold)
{
    /**
     * Changes params for the 'fn_show_picker' function
     *
     * @param string $table     Table name
     * @param string $threshold Value of the threshold after which the picker should be displayed
     */
    fn_set_hook('show_picker_pre', $table, $threshold);

    $picker = db_get_field("SELECT COUNT(*) FROM ?:$table") > $threshold ? true : false;

    /**
     * Changes result of the 'fn_show_picker' function
     *
     * @param string  $table     Table name
     * @param string  $threshold Value of the threshold after which the picker should be displayed
     * @param boolean $picker    Flag that defines if data should be displayed in picker
     */
    fn_set_hook('show_picker_post', $table, $threshold, $picker);

    return $picker;
}

/**
 * Gets categories tree beginning from category identifier defined in params or root category
 * @param array $params Categories search params
 *      category_id - Root category identifier
 *      visible - Flag that defines if only visible categories should be included
 *      current_category_id - Identifier of current node for visible categories
 *      simple - Flag that defines if category path should be getted as set of category IDs
 *      plain - Flag that defines if continues list of categories should be returned
 *      --------------------------------------
 *      Examples:
 *      Gets whole categories tree:
 *      fn_get_categories()
 *      --------------------------------------
 *      Gets subcategories tree of the category:
 *      fn_get_categories(array(
 *          'category_id' => 123
 *      ))
 *      --------------------------------------
 *      Gets all first-level nodes of the category
 *      fn_get_categories(array(
 *          'category_id' => 123,
 *          'visible' => true
 *      ))
 *      --------------------------------------
 *      Gets all visible nodes of the category, start from the root
 *      fn_get_categories(array(
 *          'category_id' => 0,
 *          'current_category_id' => 234,
 *          'visible' => true
 *      ))
 * @param string $lang_code 2-letters language code
 * @return array Categories tree
 */
function fn_get_categories($params = array(), $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params for the categories search
     *
     * @param array  $params    Categories search params
     * @param string $lang_code 2-letters language code
     */
    fn_set_hook('get_categories_pre', $params, $lang_code);

    $default_params = array (
        'category_id' => 0,
        'visible' => false,
        'current_category_id' => 0,
        'simple' => true,
        'plain' => false,
        'limit' => 0,
        'item_ids' => '',
        'group_by_level' => true,
        'get_images' => false,
        'category_delimiter' => '/',
        'get_frontend_urls' => false,
        'max_nesting_level' => null,    // null means no limitation
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'timestamp' => '?:categories.timestamp',
        'name' => '?:category_descriptions.category',
        'position' => array(
            '?:categories.position',
            '?:category_descriptions.category'
        )
    );

    $auth = & $_SESSION['auth'];

    $fields = array (
        '?:categories.category_id',
        '?:categories.parent_id',
        '?:categories.id_path',
        '?:category_descriptions.category',
        '?:categories.position',
        '?:categories.status'
    );

    if ($params['simple'] == false) {
        $fields[] = '?:categories.product_count';
    }

    if (empty($params['current_category_id']) && !empty($params['product_category_id'])) {
        $params['current_category_id'] = $params['product_category_id'];
    }

    $condition = '';

    if (fn_allowed_for('MULTIVENDOR')) {
        if (Registry::get('runtime.company_id')) {
            $company_id = Registry::get('runtime.company_id');
        } elseif (!empty($params['company_ids'])) {
            $company_id = (int) $params['company_ids'];
        }
        if (!empty($company_id)) {
            $company_data = fn_get_company_data($company_id);
            if (!empty($company_data['category_ids'])) {
                $company_condition = db_quote(' AND ?:categories.category_id IN (?n)', $company_data['category_ids']);
                $condition .= $company_condition;
            }
        }
    }

    if (AREA == 'C') {
        $_statuses = array('A'); // Show enabled products/categories
        $condition .= fn_get_localizations_condition('?:categories.localization', true);
        $condition .= " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ")";
        $condition .= db_quote(" AND ?:categories.status IN (?a)", $_statuses);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:categories.status IN (?a)", $params['status']);
    }

    if (isset($params['parent_category_id'])) {
        // set parent id, that was set in block properties
        $params['category_id'] = $params['parent_category_id'];
    }

    if ($params['visible'] == true && empty($params['b_id'])) {
        if (!empty($params['current_category_id'])) {
            $cur_id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $params['current_category_id']);
            if (!empty($cur_id_path)) {
                $parent_categories_ids = explode('/', $cur_id_path);
            }
        }
        if (!empty($params['category_id']) || empty($parent_categories_ids)) {
            $parent_categories_ids[] = $params['category_id'];
        }
        $parents_condition = db_quote(" AND ?:categories.parent_id IN (?n)", $parent_categories_ids);
    }

    // if we have company_condtion, skip $parents_condition, it will be processed later by PHP
    if (!empty($parents_condition) && empty($company_condition)) {
        $condition .= $parents_condition;
    }

    if (!empty($params['category_id'])) {
        $from_id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $params['category_id']);
        $condition .= db_quote(" AND ?:categories.id_path LIKE ?l", "$from_id_path/%");
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(' AND ?:categories.category_id IN (?n)', explode(',', $params['item_ids']));
    }

    if (!empty($params['except_id']) && (empty($params['item_ids']) || !empty($params['item_ids']) && !in_array($params['except_id'], explode(',', $params['item_ids'])))) {
        $condition .= db_quote(' AND ?:categories.category_id != ?i AND ?:categories.parent_id != ?i', $params['except_id'], $params['except_id']);
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (?:categories.timestamp >= ?i AND ?:categories.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['max_nesting_level'])) {
        $condition .= db_quote(" AND ?:categories.level <= ?i", $params['max_nesting_level']);
    }

    $limit = $join = $group_by = '';

    /**
     * Changes SQL params for the categories search
     *
     * @param array  $params    Categories search params
     * @param string $join      Join parametrs
     * @param string $condition Request condition
     * @param array  $fields    Selectable fields
     * @param string $group_by  Group by parameters
     * @param array  $sortings  Sorting fields
     * @param string $lang_code Language code
     */
    fn_set_hook('get_categories', $params, $join, $condition, $fields, $group_by, $sortings, $lang_code);

    if (!empty($params['limit'])) {
        $limit = db_quote(' LIMIT 0, ?i', $params['limit']);
    }

    $sorting = db_sort($params, $sortings, 'position', 'asc');

    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition, $group_by, $sorting, $limit);
    }

    $categories = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:categories LEFT JOIN ?:category_descriptions ON ?:categories.category_id = ?:category_descriptions.category_id AND ?:category_descriptions.lang_code = ?s $join WHERE 1 ?p $group_by $sorting ?p", 'category_id', $lang_code, $condition, $limit);

    /**
     * Process categories list after getting it
     * @param array $categories Categories list
     * @param array $params     Categories search params
     */
    fn_set_hook('get_categories_after_sql', $categories, $params);

    if (empty($categories)) {
        return array(array());
    }

    if (fn_allowed_for('ULTIMATE')) {
        // we can't build the correct tree for vendors if there are not available parent categories
        if (!empty($company_condition)) {
            $selected_ids = array_keys($categories);
            $parent_ids = array();

            // so get skipped parent categories ids
            foreach ($categories as $v) {
                if ($v['parent_id'] && !in_array($v['parent_id'], $selected_ids)) {
                    $parent_ids = array_merge($parent_ids, explode('/', $v['id_path']));
                }
            }

            if (!empty($parent_ids)) {
                // and retrieve its data
                if (Registry::get('runtime.company_id') && !empty($company_condition)) {
                    $condition = str_replace($company_condition, '', $condition);
                }
                $condition .= db_quote(' AND ?:categories.category_id IN (?a)', $parent_ids);
                $fields[] = '1 as disabled'; //mark such categories as disabled
                $parent_categories = db_get_hash_array('SELECT ' . implode(',', $fields) . " FROM ?:categories LEFT JOIN ?:category_descriptions ON ?:categories.category_id = ?:category_descriptions.category_id AND ?:category_descriptions.lang_code = ?s $join WHERE 1 ?p $group_by $sorting ?p", 'category_id', $lang_code, $condition, $limit);

                $categories = $categories + $parent_categories;
            }

            // process parents_condition if it was skipped
            if (!empty($parent_categories_ids)) {
                foreach ($categories as $k => $v) {
                    if (!in_array($v['parent_id'], $parent_categories_ids)) {
                        unset($categories[$k]);
                    }
                }
            }
        }
    }

    // @TODO remove from here, because active category may not exist in the resulting set. This is the job for controller.
    if (!empty($params['active_category_id']) && !empty($categories[$params['active_category_id']])) {
        $categories[$params['active_category_id']]['active'] = true;
        Registry::set('runtime.active_category_ids', explode('/', $categories[$params['active_category_id']]['id_path']));
    }

    $categories_list = array();
    if ($params['simple'] == true || $params['group_by_level'] == true) {
        $child_for = array_keys($categories);
        $where_condition = !empty($params['except_id']) ? db_quote(' AND category_id != ?i', $params['except_id']) : '';
        $has_children = db_get_hash_array("SELECT category_id, parent_id FROM ?:categories WHERE parent_id IN(?n) ?p", 'parent_id', $child_for, $where_condition);
    }
    // Group categories by the level (simple)
    if ($params['simple'] == true) {
        foreach ($categories as $k => $v) {
            $v['level'] = substr_count($v['id_path'], '/');
            if ((!empty($params['current_category_id']) || $v['level'] == 0) && isset($has_children[$k])) {
                $v['has_children'] = $has_children[$k]['category_id'];
            }
            $categories_list[$v['level']][$v['category_id']] = $v;
            if ($params['get_images'] == true) {
                $categories_list[$v['level']][$v['category_id']]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M', true, true, $lang_code);
            }
        }
    } elseif ($params['group_by_level'] == true) {
        // Group categories by the level (simple) and literalize path
        foreach ($categories as $k => $v) {
            $path = explode('/', $v['id_path']);
            $category_path = array();
            foreach ($path as $__k => $__v) {
                $category_path[$__v] = @$categories[$__v]['category'];
            }
            $v['category_path'] = implode($params['category_delimiter'], $category_path);
            $v['level'] = substr_count($v['id_path'], "/");
            if ((!empty($params['current_category_id']) || $v['level'] == 0) && isset($has_children[$k])) {
                $v['has_children'] = $has_children[$k]['category_id'];
            }
            $categories_list[$v['level']][$v['category_id']] = $v;
            if ($params['get_images'] == true) {
                $categories_list[$v['level']][$v['category_id']]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M', true, true, $lang_code);
            }
        }
    } else {
        $categories_list = $categories;
        if ($params['get_images'] == true) {
            foreach ($categories_list as $k => $v) {
                if ($params['get_images'] == true) {
                    $categories_list[$k]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M', true, true, $lang_code);
                }
            }
        }
    }

    ksort($categories_list, SORT_NUMERIC);
    $categories_list = array_reverse($categories_list);

    foreach ($categories_list as $level => $v) {
        foreach ($v as $k => $data) {
            if (isset($data['parent_id']) && isset($categories_list[$level + 1][$data['parent_id']])) {
                $categories_list[$level + 1][$data['parent_id']]['subcategories'][] = $categories_list[$level][$k];
                unset($categories_list[$level][$k]);
            }
        }
    }

    if (!empty($params['get_frontend_urls'])) {
        foreach ($categories_list as &$category) {
            $category['url'] = fn_url('categories.view?category_id=' . $category['category_id'], 'C');
        }
    }

    if ($params['group_by_level'] == true) {
        $categories_list = array_pop($categories_list);
    }

    if ($params['plain'] == true) {
        $categories_list = fn_multi_level_to_plain($categories_list, 'subcategories');
    }

    if (!empty($params['item_ids'])) {
        $categories_list = fn_sort_by_ids($categories_list, explode(',', $params['item_ids']), 'category_id');
    }

    if (!empty($params['add_root'])) {
        array_unshift($categories_list, array('category_id' => 0, 'category' => $params['add_root']));
    }

    /**
     * Process categories list before cutting second and fird levels
     *
     * @param array $categories_list Categories list
     * @param array $params          Categories search params
     */
    fn_set_hook('get_categories_before_cut_levels', $categories_list, $params);

    fn_dropdown_appearance_cut_second_third_levels($categories_list, 'subcategories', $params);

    /**
     * Process final category list
     *
     * @param array  $categories_list Categories list
     * @param array  $params          Categories search params
     * @param string $lang_code       Language code
     */
    fn_set_hook('get_categories_post', $categories_list, $params, $lang_code);

    // process search results
    if (!empty($params['save_view_results'])) {
        $request = $params;
        $request['page'] = 1;
        $categories_res = ($params['plain'] == true) ? $categories_list : fn_multi_level_to_plain($categories_list, 'subcategories');
        foreach ($categories_res as $key => $item) {
            if (empty($item['category_id'])) {
                unset($categories_res[$key]);
            }
        }
        $request['total_items'] = $request['items_per_page'] = count($categories_res);
        LastView::instance()->processResults('categories', $categories_res, $request);
    }

    return array($categories_list, $params);
}

/**
 * Recursively sorts an array using a user-supplied comparison function
 *
 * @param array $array Array for sorting
 * @param string $key Key of subarray for sorting
 * @param callback $function Comparison function
 */
function fn_sort(&$array, $key, $function)
{
    usort($array, $function);
    foreach ($array as $k => $v) {
        if (!empty($v[$key])) {
            fn_sort($array[$k][$key], $key, $function);
        }
    }
}

/**
 * Gets full category data by its id
 *
 * @param int $category_id ID of category
 * @param string $lang_code 2-letters language code
 * @param string $field_list List of categories table' fields. If empty, data from all fields will be returned.
 * @param boolean $get_main_pair Get or not category image
 * @param boolean $skip_company_condition Select data for other stores categories. By default is false. This flag is used in ULT for displaying common categories in picker.
 * @return mixed Array with category data.
 */
function fn_get_category_data($category_id = 0, $lang_code = CART_LANGUAGE, $field_list = '', $get_main_pair = true, $skip_company_condition = false, $preview = false)
{
    /**
     * Changes select category data conditions
     *
     * @param int     $category_id            Category ID
     * @param array   $field_list             List of fields for retrieving
     * @param boolean $get_main_pair          Get or not category image
     * @param boolean $skip_company_condition Select data for other stores categories. By default is false. This flag is used in ULT for displaying common categories in picker.
     * @param string  $lang_code              2-letters language code
     */
    fn_set_hook('get_category_data_pre', $category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code);

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
        $company_data = Registry::get('runtime.company_data');
        if (!empty($company_data['categories'])) {
            $allowed_categories = explode(',', $company_data['categories']);
            if (!in_array($category_id, $allowed_categories)) {
                return false;
            }
        }
    }

    $auth = & $_SESSION['auth'];

    $conditions = '';
    if (AREA == 'C' && !$preview) {
        $conditions = "AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ")";
    }

    if (empty($field_list)) {
        $descriptions_list = "?:category_descriptions.*";
        $field_list = "?:categories.*, $descriptions_list";
    }

    if (fn_allowed_for('ULTIMATE') && !$skip_company_condition) {
        $conditions .= fn_get_company_condition('?:categories.company_id');
    }

    $join = '';

    /**
     * Changes SQL parameters before select category data
     *
     * @param int    $category_id Category ID
     * @param array  $field_list  SQL fields to be selected in an SQL-query
     * @param string $join        String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $lang_code   2-letters language code
     */
    fn_set_hook('get_category_data', $category_id, $field_list, $join, $lang_code);

    $category_data = db_get_row("SELECT $field_list FROM ?:categories LEFT JOIN ?:category_descriptions ON ?:category_descriptions.category_id = ?:categories.category_id AND ?:category_descriptions.lang_code = ?s ?p WHERE ?:categories.category_id = ?i ?p", $lang_code, $join, $category_id, $conditions);

    if (!empty($category_data)) {
        $category_data['category_id'] = $category_id;

        // Generate meta description automatically
        if (empty($category_data['meta_description']) && defined('AUTO_META_DESCRIPTION') && AREA != 'A') {
            $category_data['meta_description'] = !empty($category_data['description']) ? fn_generate_meta_description($category_data['description']) : '';
        }

        if ($get_main_pair == true) {
            $category_data['main_pair'] = fn_get_image_pairs($category_id, 'category', 'M', true, true, $lang_code);
        }

        if (!empty($category_data['selected_layouts'])) {
            $category_data['selected_layouts'] = unserialize($category_data['selected_layouts']);
        } else {
            $category_data['selected_layouts'] = array();
        }
    }

    /**
     * Changes category data
     *
     * @param int     $category_id            Category ID
     * @param array   $field_list             List of fields for retrieving
     * @param boolean $get_main_pair          Get or not category image
     * @param boolean $skip_company_condition Select data for other stores categories. By default is false. This flag is used in ULT for displaying common categories in picker.
     * @param string  $lang_code              2-letters language code
     * @param array   $category_data          Array with category fields
     */
    fn_set_hook('get_category_data_post', $category_id, $field_list, $get_main_pair, $skip_company_condition, $lang_code, $category_data);

    return (!empty($category_data) ? $category_data : false);
}

/**
 * Gets category name by category identifier
 *
 * @param int/array $category_id Category identifier or array of category identifiers
 * @param string $lang_code 2-letters language code
 * @param boolean $as_array Flag if false one category name is returned as simple string, if true category names are always returned as array
 * @return string/array Category name or array with category names
 */
function fn_get_category_name($category_id = 0, $lang_code = CART_LANGUAGE, $as_array = false)
{
    /**
     * Changes parameters for getting category name
     *
     * @param int/array $category_id Category identifier or array of category identifiers
     * @param string    $lang_code   2-letters language code
     * @param boolean   $as_array    Flag if false one category name is returned as simple string, if true category names are always returned as array
     */
    fn_set_hook('get_category_name_pre', $category_id, $lang_code, $as_array);

    $name = array();

    if (!empty($category_id)) {
        if (!is_array($category_id) && strpos($category_id, ',') !== false) {
            $category_id = explode(',', $category_id);
        }
        if (is_array($category_id) || $as_array == true) {
            $name = db_get_hash_single_array("SELECT category_id, category FROM ?:category_descriptions WHERE category_id IN (?n) AND lang_code = ?s", array('category_id', 'category'), $category_id, $lang_code);
        } else {
            $name = db_get_field("SELECT category FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $category_id, $lang_code);
        }
    }

    /**
     * Changes category names
     *
     * @param int|array    $category_id Category identifier or array of category identifiers
     * @param string       $lang_code   2-letters language code
     * @param boolean      $as_array    Flag if false one category name is returned as simple string, if true category names are always returned as array
     * @param string|array $name        Category name or array with category names
     */
    fn_set_hook('get_category_name_post', $category_id, $lang_code, $as_array, $name);

    return $name;
}

/**
 * Gets category path by category identifier
 *
 * @param int $category_id Category identifier
 * @param string $lang_code 2-letters language code
 * @param string $path_separator String character(s) separating the catergories
 * @return string Category path
 */
function fn_get_category_path($category_id = 0, $lang_code = CART_LANGUAGE, $path_separator = '/')
{
    /**
     * Change parameters for getting category path
     *
     * @param int    $category_id    Category identifier
     * @param string $lang_code      2-letters language code
     * @param string $path_separator String character(s) separating the catergories
     */
    fn_set_hook('fn_get_category_path_pre', $category_id, $lang_code, $path_separator);

    $category_path = false;

    if (!empty($category_id)) {

        $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);

        $category_names = db_get_hash_single_array(
            "SELECT category_id, category FROM ?:category_descriptions WHERE category_id IN (?n) AND lang_code = ?s",
            array('category_id', 'category'), explode('/', $id_path), $lang_code
        );

        $path = explode('/', $id_path);
        $_category_path = '';
        foreach ($path as $v) {
            $_category_path .= $category_names[$v] . $path_separator;
        }
        $_category_path = rtrim($_category_path, $path_separator);

        $category_path = (!empty($_category_path) ? $_category_path : false);
    }

    /**
     * Change category path
     *
     * @param int    $category_id    Category identifier
     * @param string $lang_code      2-letters language code
     * @param string $path_separator String character(s) separating the catergories
     * @param string $category_path  Category path
     */
    fn_set_hook('fn_get_category_path_post', $category_id, $lang_code, $path_separator, $category_path);

    return $category_path;
}

/**
 * Removes category by identifier
 *
 * @param int $category_id Category identifier
 * @param boolean $recurse Flag that defines if category should be deleted recursively
 * @return int/boolean Identifiers of deleted categories or false if categories were not found
 */
function fn_delete_category($category_id, $recurse = true)
{
    /**
     * Actions before category and its related data removal
     *
     * @param  int         $category_id Category identifier to delete
     * @param  boolean     $recurse     Flag that defines if category should be deleted recursively
     * @return int|boolean Identifiers of deleted categories or false if categories were not found
     */
    fn_set_hook('delete_category_pre', $category_id, $recurse);

    if (empty($category_id)) {
        return false;
    }

    // Log category deletion
    fn_log_event('categories', 'delete', array(
        'category_id' => $category_id,
    ));

    // Delete all subcategories
    if ($recurse == true) {
        $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
        $category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE category_id = ?i OR id_path LIKE ?l", $category_id, "$id_path/%");
    } else {
        $category_ids[] = $category_id;
    }

    foreach ($category_ids as $k => $category_id) {
        /**
         * Process category delete (run before category is deleted)
         *
         * @param int $category_id Category identifier
         */
        fn_set_hook('delete_category_before', $category_id);

        Block::instance()->removeDynamicObjectdata('categories', $category_id);

        // Deleting category
        db_query("DELETE FROM ?:categories WHERE category_id = ?i", $category_id);
        db_query("DELETE FROM ?:category_descriptions WHERE category_id = ?i", $category_id);

        // Deleting additional product associations without deleting products itself
        db_query("DELETE FROM ?:products_categories WHERE category_id = ?i AND link_type = 'A'", $category_id);

        // Remove this category from features assignments
        db_query("UPDATE ?:product_features SET categories_path = ?p", fn_remove_from_set('categories_path', $category_id));

        if (!fn_allowed_for('ULTIMATE')) {
            // Deleting main products association with deleting products
            $products_to_delete = db_get_fields("SELECT product_id FROM ?:products_categories WHERE category_id = ?i AND link_type = 'M'", $category_id);
            if (!empty($products_to_delete)) {
                foreach ($products_to_delete as $key => $value) {
                    fn_delete_product($value);
                }
            }
        }

        // Deleting category images
        fn_delete_image_pairs($category_id, 'category');

        /**
         * Process category delete (run after category is deleted)
         *
         * @param int $category_id Category identifier
         */
        fn_set_hook('delete_category_after', $category_id);
    }

    /**
     * Actions after category and its related data removal
     *
     * @param int     $category_id  Category identifier to delete
     * @param boolean $recurse      Flag that defines if category should be deleted recursively
     * @param int     $category_ids Category identifiers that were removed
     */
    fn_set_hook('delete_category_post', $category_id, $recurse, $category_ids);

    return $category_ids; // Returns ids of deleted categories
}

/**
 * Removes product by identifier
 *
 * @param int $product_id Product identifier
 * @return boolean Flag that defines if product was deleted
 */
function fn_delete_product($product_id)
{
    $status = true;
    /**
     * Check product delete (run before product is deleted)
     *
     * @param int     $product_id Product identifier
     * @param boolean $status     Flag determines if product can be deleted, if false product is not deleted
     */
    fn_set_hook('delete_product_pre', $product_id, $status);

    $product_deleted = false;

    if (!empty($product_id)) {

        if (!fn_check_company_id('products', 'product_id', $product_id)) {
            fn_set_notification('W', __('warning'), __('access_denied'));

            return false;
        }

        if ($status == false) {
            return false;
        }

        Block::instance()->removeDynamicObjectData('products', $product_id);

        // Log product deletion
        fn_log_event('products', 'delete', array(
            'product_id' => $product_id,
        ));

        // Delete product files
        fn_delete_product_files(0, $product_id);

        // Delete product folders
        fn_delete_product_file_folders(0, $product_id);

        $category_ids = db_get_fields("SELECT category_id FROM ?:products_categories WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:products_categories WHERE product_id = ?i", $product_id);
        fn_update_product_count($category_ids);

        $res = db_query("DELETE FROM ?:products WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_descriptions WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_prices WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_features_values WHERE product_id = ?i", $product_id);

        if (!fn_allowed_for('ULTIMATE:FREE')) {
            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $product_id);
        }
        db_query("DELETE FROM ?:product_popularity WHERE product_id = ?i", $product_id);

        fn_delete_image_pairs($product_id, 'product');

        // Delete product options and inventory records for this product
        fn_poptions_delete_product($product_id);

        // Executing delete_product functions from active addons

        $product_deleted = $res;
    }

    /**
     * Process product delete (run after product is deleted)
     *
     * @param int  $product_id      Product identifier
     * @param bool $product_deleted True if product was deleted successfully, false otherwise
     */
    fn_set_hook('delete_product_post', $product_id, $product_deleted);

    return $product_deleted;
}

/**
 * Check if product exists in database.
 *
 * @param int $product_id
 * @return bool
 */
function fn_product_exists($product_id)
{
    $result = true;
    fn_set_hook('product_exists', $product_id, $result);

    $res = db_get_field('SELECT COUNT(*) FROM ?:products WHERE product_id = ?i', $product_id);

    return $result && $res;
}

/**
 * Global products update
 *
 * @param array $update_data List of updated fields and product_ids
 * @return boolean Always true
 */
function fn_global_update_products($update_data)
{
    $table = $field = $value = $type = array();
    $msg = '';

    /**
     * Global update products data (running before fn_global_update_products() function)
     *
     * @param array  $update_data List of updated fields and product_ids
     * @param array  $table       List of table names to be updated
     * @param array  $field       List of SQL field names to be updated
     * @param array  $value       List of new fields values
     * @param array  $type        List of field types absolute or persentage
     * @param string $msg         Message containing the information about the changes made
     */
    fn_set_hook('global_update_products_pre', $update_data, $table, $field, $value, $type, $msg);

    $all_product_notify = false;
    $currencies = Registry::get('currencies');

    if (!empty($update_data['product_ids'])) {
        $update_data['product_ids'] = explode(',', $update_data['product_ids']);
        if (fn_allowed_for('MULTIVENDOR') && !fn_company_products_check($update_data['product_ids'], true)) {
            return false;
        }
    } elseif (fn_allowed_for('MULTIVENDOR')) {
        $all_product_notify = true;
        $update_data['product_ids'] = db_get_fields("SELECT product_id FROM ?:products WHERE 1 ?p", fn_get_company_condition('?:products.company_id'));
    }

    // Update prices
    if (!empty($update_data['price'])) {
        $table[] = '?:product_prices';
        $field[] = 'price';
        $value[] = $update_data['price'];
        $type[] = $update_data['price_type'];

        $msg .= ($update_data['price'] > 0 ? __('price_increased') : __('price_decreased')) . ' ' . abs($update_data['price']) . ($update_data['price_type'] == 'A' ? $currencies[CART_PRIMARY_CURRENCY]['symbol'] : '%') . '.<br />';
    }

    // Update list prices
    if (!empty($update_data['list_price'])) {
        $table[] = '?:products';
        $field[] = 'list_price';
        $value[] = $update_data['list_price'];
        $type[] = $update_data['list_price_type'];

        $msg .= ($update_data['list_price'] > 0 ? __('list_price_increased') : __('list_price_decreased')) . ' ' . abs($update_data['list_price']) . ($update_data['list_price_type'] == 'A' ? $currencies[CART_PRIMARY_CURRENCY]['symbol'] : '%') . '.<br />';
    }

    // Update amount
    if (!empty($update_data['amount'])) {
        $table[] = '?:products';
        $field[] = 'amount';
        $value[] = $update_data['amount'];
        $type[] = 'A';

        $table[] = '?:product_options_inventory';
        $field[] = 'amount';
        $value[] = $update_data['amount'];
        $type[] = 'A';

        $msg .= ($update_data['amount'] > 0 ? __('amount_increased') : __('amount_decreased')) .' ' . abs($update_data['amount']) . '.<br />';
    }

    /**
     * Global update products data (running inside fn_global_update_products() function before fields update)
     *
     * @param array  $table       List of table names to be updated
     * @param array  $field       List of SQL field names to be updated
     * @param array  $value       List of new fields values
     * @param array  $type        List of field types absolute or persentage
     * @param string $msg         Message containing the information about the changes made
     * @param array  $update_data List of updated fields and product_ids
     */
    fn_set_hook('global_update_products', $table, $field, $value, $type, $msg, $update_data);

    $where = !empty($update_data['product_ids']) ? db_quote(" AND product_id IN (?n)", $update_data['product_ids']) : '';

    foreach ($table as $k => $v) {
        $_value = db_quote("?d", $value[$k]);
        $sql_expression = $type[$k] == 'A' ? ($field[$k] . ' + ' . $_value) : ($field[$k] . ' * (1 + ' . $_value . '/ 100)');

        if (($type[$k] == 'A') && !empty($update_data['product_ids']) && ($_value > 0)) {
            foreach ($update_data['product_ids'] as $product_id) {
                $send_notification = false;
                $product = fn_get_product_data($product_id, $auth, DESCR_SL, '', true, true, true, true);

                if (($product['tracking'] == ProductTracking::TRACK_WITHOUT_OPTIONS) && ($product['amount'] <= 0)) {
                    $send_notification = true;
                } elseif ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                    $inventory = db_get_array("SELECT * FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
                    foreach ($inventory as $inventory_item) {
                        if ($inventory_item['amount'] <= 0) {
                            $send_notification = true;
                        }
                    }
                }

                if ($send_notification) {
                    fn_send_product_notifications($product_id);
                }
            }
        }

        if (fn_allowed_for('ULTIMATE') && $field[$k] == 'price') {
            $company_condition = "";
            if (Registry::get('runtime.company_id')) {
                $company_condition .= db_quote(" AND company_id = ?i", Registry::get('runtime.company_id'));
            }

            db_query("UPDATE ?p SET ?p = IF(?p < 0, 0, ?p) WHERE product_id IN (SELECT product_id FROM ?:products WHERE 1 ?p ?p)", $v, $field[$k], $sql_expression, $sql_expression, $where, $company_condition);

            $sql_expression = $type[$k] == 'A' ? '`price` + ?d' : '`price` * (1 + ?d / 100)';
            $sql_expression = db_quote($sql_expression, $update_data['price']);

            db_query("UPDATE ?:ult_product_prices SET `price` = IF(?p < 0, 0, ?p) WHERE 1 ?p ?p", $sql_expression, $sql_expression, $where, $company_condition);
        } else {

            db_query("UPDATE ?p SET ?p = IF(?p < 0, 0, ?p) WHERE 1 ?p", $v, $field[$k], $sql_expression, $sql_expression, $where);

        }
    }

    /**
     * Global update products data (running after fn_global_update_products() function)
     *
     * @param string $msg         Message containing the information about the changes made
     * @param array  $update_data List of updated fields and product_ids
     */
    fn_set_hook('global_update_products_post', $msg, $update_data);

    if (empty($update_data['product_ids']) || $all_product_notify) {
        fn_set_notification('N', __('notice'), __('all_products_have_been_updated') . '<br />' . $msg);
    } else {
        fn_set_notification('N', __('notice'), __('text_products_updated'));
    }

    return true;
}

/**
 * Adds or updates product
 *
 * @param array $product_data Product data
 * @param int $product_id Product identifier
 * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
 * @return mixed ID of created/updated product or false in case of error
 */
function fn_update_product($product_data, $product_id = 0, $lang_code = CART_LANGUAGE)
{
    $can_update = true;
    /**
     * Update product data (running before fn_update_product() function)
     *
     * @param array   $product_data Product data
     * @param int     $product_id   Product identifier
     * @param string  $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param boolean $can_update   Flag, allows addon to forbid to create/update product
     */
    fn_set_hook('update_product_pre', $product_data, $product_id, $lang_code, $can_update);

    if ($can_update == false) {
        return false;
    }

    if (fn_allowed_for('ULTIMATE')) {
        // check that product owner was not changed by store administrator
        if (Registry::get('runtime.company_id') || empty($product_data['company_id'])) {
            $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);
            if (!empty($product_company_id)) {
                $product_data['company_id'] = $product_company_id;
            } else {
                if (Registry::get('runtime.company_id')) {
                    $product_company_id = $product_data['company_id'] = Registry::get('runtime.company_id');
                } else {
                    $product_company_id = $product_data['company_id'] = fn_get_default_company_id();
                }
            }
        } else {
            $product_company_id = $product_data['company_id'];
        }

        if (!empty($product_data['category_ids']) && !fn_check_owner_categories($product_company_id, $product_data['category_ids'])) {
            fn_set_notification('E', __('error'), __('product_must_have_owner_category'));

            return false;
        }

        if (fn_ult_is_shared_product($product_id) == 'Y') {
            $_product_id = fn_ult_update_shared_product($product_data, $product_id, Registry::ifGet('runtime.company_id', $product_company_id), $lang_code);
        }
    }

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && !empty($product_company_id) && Registry::get('runtime.company_id') != $product_company_id && !empty($_product_id)) {
        $product_id = $_product_id;
        $create = false;
    } else {
        $product_data['updated_timestamp'] = time();

        $_data = $product_data;

        if (!empty($product_data['timestamp'])) {
            $_data['timestamp'] = fn_parse_date($product_data['timestamp']); // Minimal data for product record
        } elseif (empty($product_id) || isset($product_data['timestamp'])) {
            $_data['timestamp'] = time();
        }

        if (empty($product_id) && Registry::get('runtime.company_id')) {
            $_data['company_id'] = Registry::get('runtime.company_id');
        }

        if (!empty($product_data['avail_since'])) {
            $_data['avail_since'] = fn_parse_date($product_data['avail_since']);
        }

        if (isset($product_data['tax_ids'])) {
            $_data['tax_ids'] = empty($product_data['tax_ids']) ? '' : fn_create_set($product_data['tax_ids']);
        }

        if (isset($product_data['localization'])) {
            $_data['localization'] = empty($product_data['localization']) ? '' : fn_implode_localizations($_data['localization']);
        }

        if (isset($product_data['usergroup_ids'])) {
            $_data['usergroup_ids'] = empty($product_data['usergroup_ids']) ? '0' : implode(',', $_data['usergroup_ids']);
        }

        if (!empty($product_data['list_qty_count']) && $product_data['list_qty_count'] < 0) {
            $_data['list_qty_count'] = 0;
        }

        if (!empty($product_data['qty_step']) && $product_data['qty_step'] < 0) {
            $_data['qty_step'] = 0;
        }

        if (!empty($product_data['min_qty'])) {
            $_data['min_qty'] = fn_ceil_to_step(abs($product_data['min_qty']), $_data['qty_step']);
        }

        if (!empty($product_data['max_qty'])) {
            $_data['max_qty'] = fn_ceil_to_step(abs($product_data['max_qty']), $_data['qty_step']);
        }

        if (Registry::get('settings.General.inventory_tracking') == "N" && isset($_data['tracking'])) {
            unset($_data['tracking']);
        }

        if (Registry::get('settings.General.allow_negative_amount') == 'N' && isset($_data['amount'])) {
            $_data['amount'] = abs($_data['amount']);
        }

        $shipping_params = array();
        if (!empty($product_id)) {
            $shipping_params = db_get_field('SELECT shipping_params FROM ?:products WHERE product_id = ?i', $product_id);
            if (!empty($shipping_params)) {
                $shipping_params = unserialize($shipping_params);
            }
        }

        // Save the product shipping params
        $_shipping_params = array(
            'min_items_in_box' => isset($_data['min_items_in_box']) ? intval($_data['min_items_in_box']) : (!empty($shipping_params['min_items_in_box']) ? $shipping_params['min_items_in_box'] : 0),
            'max_items_in_box' => isset($_data['max_items_in_box']) ? intval($_data['max_items_in_box']) : (!empty($shipping_params['max_items_in_box']) ? $shipping_params['max_items_in_box'] : 0),
            'box_length' => isset($_data['box_length']) ? intval($_data['box_length']) : (!empty($shipping_params['box_length']) ? $shipping_params['box_length'] : 0),
            'box_width' => isset($_data['box_width']) ? intval($_data['box_width']) : (!empty($shipping_params['box_width']) ? $shipping_params['box_width'] : 0),
            'box_height' => isset($_data['box_height']) ? intval($_data['box_height']) : (!empty($shipping_params['box_height']) ? $shipping_params['box_height'] : 0),
        );

        $_data['shipping_params'] = serialize($_shipping_params);
        unset($_shipping_params);

        // add new product
        if (empty($product_id)) {
            $create = true;
            $product_data['create'] = true;
            // product title can't be empty and not set product_id
            if (empty($product_data['product']) || !empty($product_data['product_id'])) {
                fn_set_notification('E', __('error'), __('need_product_name'));

                return false;
            }

            $product_id = db_query("INSERT INTO ?:products ?e", $_data);

            if (empty($product_id)) {
                $product_id = false;
            }

            //
            // Adding same product descriptions for all cart languages
            //
            $_data = $product_data;
            $_data['product_id'] =	$product_id;
            $_data['product'] = trim($_data['product'], " -");

            foreach (fn_get_translation_languages() as $_data['lang_code'] => $_v) {
                db_query("INSERT INTO ?:product_descriptions ?e", $_data);
            }

        // update product
        } else {
            $create = false;
            if (isset($product_data['product']) && empty($product_data['product'])) {
                unset($product_data['product']);
            }

            $old_product_data = fn_get_product_data($product_id, $auth, $lang_code, '', false, false, false, false);

            if (isset($old_product_data['amount']) && isset($_data['amount']) && ($old_product_data['amount'] <= 0) && ($_data['amount'] > 0)) {
                fn_send_product_notifications($product_id);
            }

            $arow = db_query("UPDATE ?:products SET ?u WHERE product_id = ?i", $_data, $product_id);

            $_data = $product_data;
            if (!empty($_data['product'])) {
                $_data['product'] = trim($_data['product'], " -");
            }
            db_query("UPDATE ?:product_descriptions SET ?u WHERE product_id = ?i AND lang_code = ?s", $_data, $product_id, $lang_code);

            if ($arow === false) {
                fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('product'))),'','404');
                $product_id = false;
            }
        }

        if ($product_id) {
            // Log product add/update
            fn_log_event('products', !empty($create) ? 'create' : 'update', array(
                'product_id' => $product_id,
            ));

            // Update product features value
            $product_data['product_features'] = !empty($product_data['product_features']) ? $product_data['product_features'] : array();
            $product_data['add_new_variant'] = !empty($product_data['add_new_variant']) ? $product_data['add_new_variant'] : array();
            fn_update_product_features_value($product_id, $product_data['product_features'], $product_data['add_new_variant'], $lang_code);

            // Update product prices
            $product_data = fn_update_product_prices($product_id, $product_data);

            if (!empty($product_data['popularity'])) {
                $_data = array (
                    'product_id' => $product_id,
                    'total' => intval($product_data['popularity'])
                );

                db_query("INSERT INTO ?:product_popularity ?e ON DUPLICATE KEY UPDATE total = ?i", $_data, $product_data['popularity']);
            }

            fn_update_product_categories($product_id, $product_data);

            // Update main images pair
            fn_attach_image_pairs('product_main', 'product', $product_id, $lang_code);

            // Update additional images
            fn_attach_image_pairs('product_additional', 'product', $product_id, $lang_code);

            // Adding new additional images
            fn_attach_image_pairs('product_add_additional', 'product', $product_id, $lang_code);

            if (fn_allowed_for('ULTIMATE')) {
                fn_check_and_update_product_sharing($product_id);
            }
        }
    }

    /**
     * Update product data (running after fn_update_product() function)
     *
     * @param array   $product_data Product data
     * @param int     $product_id   Product integer identifier
     * @param string  $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param boolean $create       Flag determines if product was created (true) or just updated (false).
     */
    fn_set_hook('update_product_post', $product_data, $product_id, $lang_code, $create);

    return (int) $product_id;
}

function fn_update_product_features_value($product_id, $product_features, $add_new_variant, $lang_code)
{
    if (empty($product_features)) {
        return false;
    }

    $i_data = array(
        'product_id' => $product_id,
        'lang_code' => $lang_code
    );

    foreach ($product_features as $feature_id => $value) {

        // Check if feature is applicable for this product
        $id_paths = db_get_fields("SELECT ?:categories.id_path FROM ?:products_categories LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id WHERE product_id = ?i", $product_id);

        $_params = array(
            'category_ids' => array_unique(explode('/', implode('/', $id_paths))),
            'feature_id' => $feature_id
        );
        list($_feature) = fn_get_product_features($_params);

        if (empty($_feature)) {
            $_feature = db_get_field("SELECT description FROM ?:product_features_descriptions WHERE feature_id = ?i AND lang_code = ?s", $feature_id, $lang_code);
            $_product = db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $product_id, $lang_code);
            fn_set_notification('E', __('error'), __('product_feature_cannot_assigned', array(
                '[feature_name]' => $_feature,
                '[product_name]' => $_product
            )));

            continue;
        }

        $i_data['feature_id'] = $feature_id;
        unset($i_data['value']);
        unset($i_data['variant_id']);
        unset($i_data['value_int']);
        $feature_type = db_get_field("SELECT feature_type FROM ?:product_features WHERE feature_id = ?i", $feature_id);

        // Delete variants in current language
        if ($feature_type == 'T') {
            db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i AND lang_code = ?s", $feature_id, $product_id, $lang_code);
        } else {
            db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id = ?i", $feature_id, $product_id);
        }

        if ($feature_type == 'D') {
            $i_data['value_int'] = !empty($value) ? fn_parse_date($value) : '';
        } elseif ($feature_type == 'M') {
            if (!empty($add_new_variant[$feature_id]['variant']) || (isset($add_new_variant[$feature_id]['variant']) && $add_new_variant[$feature_id]['variant'] === '0')) {
                $value = empty($value) ? array() : $value;
                $value[] = fn_add_feature_variant($feature_id, $add_new_variant[$feature_id]);
            }
            if (!empty($value)) {
                foreach ($value as $variant_id) {
                    foreach (fn_get_translation_languages() as $i_data['lang_code'] => $_d) { // insert for all languages
                        $i_data['variant_id'] = $variant_id;
                        db_query("REPLACE INTO ?:product_features_values ?e", $i_data);
                    }
                }
            }
            continue;
        } elseif (in_array($feature_type, array('S', 'N', 'E'))) {
            if (!empty($add_new_variant[$feature_id]['variant']) || (isset($add_new_variant[$feature_id]['variant']) && $add_new_variant[$feature_id]['variant'] === '0')) {
                $i_data['variant_id'] = fn_add_feature_variant($feature_id, $add_new_variant[$feature_id]);
                $i_data['value_int'] = $add_new_variant[$feature_id]['variant'];
            } elseif (!empty($value) && $value != 'disable_select') {
                if ($feature_type == 'N') {
                    $i_data['value_int'] = db_get_field("SELECT variant FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i AND lang_code = ?s", $value, $lang_code);
                }
                $i_data['variant_id'] = $value;
            } else {
                continue;
            }
        } else {
            if ($value == '') {
                continue;
            }
            if ($feature_type == 'O') {
                $i_data['value_int'] = $value;
            } else {
                $i_data['value'] = $value;
            }
        }

        if ($feature_type != 'T') { // feature values are common for all languages, except text (T)
            foreach (fn_get_translation_languages() as $i_data['lang_code'] => $_d) {
                db_query("REPLACE INTO ?:product_features_values ?e", $i_data);
            }
        } else { // for text feature, update current language only
            $i_data['lang_code'] = $lang_code;
            db_query("INSERT INTO ?:product_features_values ?e", $i_data);
        }
    }

    return true;
}

/**
 * Recalculates and updates products quantity in categories
 *
 * @param array $category_ids List of categories identifiers for update
 * @return boolean Flag determines if products quantity was updated
 */
function fn_update_product_count($category_ids)
{
    if (!empty($category_ids)) {

        $category_ids = array_unique($category_ids);

        /**
         * Update product count (running before update)
         *
         * @param array $category_ids List of category ids for update
         */
        fn_set_hook('update_product_count_pre', $category_ids);

        foreach ($category_ids as $category_id) {
            $product_count = db_get_field("SELECT COUNT(*) FROM ?:products_categories WHERE category_id = ?i", $category_id);
            db_query("UPDATE ?:categories SET product_count = ?i WHERE category_id = ?i", $product_count, $category_id);
        }

        /**
         * Update product count (running after update)
         *
         * @param array $category_ids List of category ids for update
         */
        fn_set_hook('update_product_count_post', $category_ids);

        return true;
    }

    return false;
}


/**
 * Adds or updates category
 *
 * @param array $category_data Category data
 * @param int $category_id Category identifier
 * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
 * @return int New or updated category identifier
 */
function fn_update_category($category_data, $category_id = 0, $lang_code = CART_LANGUAGE)
{
    /**
     * Update category data (running before fn_update_category() function)
     *
     * @param array  $category_data Category data
     * @param int    $category_id   Category identifier
     * @param string $lang_code     Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('update_category_pre', $category_data, $category_id, $lang_code);

    // category title required
    if (empty($category_data['category'])) {
        //return false; // FIXME: management page doesn't have category name
    }

    if (isset($category_data['localization'])) {
        $category_data['localization'] = empty($category_data['localization']) ? '' : fn_implode_localizations($category_data['localization']);
    }
    if (isset($category_data['usergroup_ids'])) {
        $category_data['usergroup_ids'] = empty($category_data['usergroup_ids']) ? '0' : implode(',', $category_data['usergroup_ids']);
    }
    if (fn_allowed_for('ULTIMATE')) {
        fn_set_company_id($category_data);
    }

    $_data = $category_data;
    unset($_data['parent_id']);

    if (isset($category_data['timestamp'])) {
        $_data['timestamp'] = fn_parse_date($category_data['timestamp']);
    }

    if (isset($_data['position']) && empty($_data['position']) && $_data['position'] != '0' && isset($category_data['parent_id'])) {
        $_data['position'] = db_get_field("SELECT max(position) FROM ?:categories WHERE parent_id = ?i", $category_data['parent_id']);
        $_data['position'] = $_data['position'] + 10;
    }

    if (isset($_data['selected_layouts'])) {
        $_data['selected_layouts'] = serialize($_data['selected_layouts']);
    }

    if (isset($_data['use_custom_templates']) && $_data['use_custom_templates'] == 'N') {
        // Clear the layout settings if the category custom templates were disabled
        $_data['product_columns'] = $_data['selected_layouts'] = $_data['default_layout'] = '';
    }

    // create new category
    if (empty($category_id)) {

        if (fn_allowed_for('ULTIMATE') && empty($_data['company_id'])) {
            fn_set_notification('E', __('error'), __('need_company_id'));

            return false;
        }

        $create = true;

        $category_id = db_query("INSERT INTO ?:categories ?e", $_data);
        $_data['category_id'] = $category_id;

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:category_descriptions ?e", $_data);
        }

        $category_data['parent_id'] = !empty($category_data['parent_id']) ? $category_data['parent_id'] : 0;

    // update existing category
    } else {

        $category_data['old_company_id'] = db_get_field("SELECT company_id FROM ?:categories WHERE category_id = ?i", $category_id);

        $arow = db_query("UPDATE ?:categories SET ?u WHERE category_id = ?i", $_data, $category_id);
        $arow_description = db_query("UPDATE ?:category_descriptions SET ?u WHERE category_id = ?i AND lang_code = ?s", $_data, $category_id, $lang_code);

        if ($arow === false && $arow_description === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('category'))),'','404');
            $category_id = false;
        }
    }

    if ($category_id) {

        // regenerate id_path for all child categories of the updated category
        if (isset($category_data['parent_id'])) {
            fn_change_category_parent($category_id, intval($category_data['parent_id']));
        }

        // Log category add/update
        fn_log_event('categories', !empty($create) ? 'create' : 'update', array(
            'category_id' => $category_id,
        ));

        // Assign usergroup to all subcategories
        if (!empty($_data['usergroup_to_subcats']) && $_data['usergroup_to_subcats'] == 'Y') {
            $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
            db_query("UPDATE ?:categories SET usergroup_ids = ?s WHERE id_path LIKE ?l", $_data['usergroup_ids'], "$id_path/%");
        }
    }

    /**
     * Update category data (running after fn_update_category() function)
     *
     * @param array  $category_data Category data
     * @param int    $category_id   Category identifier
     * @param string $lang_code     Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('update_category_post', $category_data, $category_id, $lang_code);

    return $category_id;

}

/**
 * Changes category's parent to another category. Modifies "id_path and "level" attributes of category and its children.
 *
 * @param int $category_id Category identifier
 * @param int $new_parent_id Identifier of new category parent
 * @return bool True on success, false otherwise
 */
function fn_change_category_parent($category_id, $new_parent_id)
{
    if (empty($category_id) || $category_id == $new_parent_id) { return false; }
    /**
     * Adds additional actions before category parent updating
     *
     * @param int $category_id   Category identifier
     * @param int $new_parent_id Identifier of new category parent
     */
    fn_set_hook('update_category_parent_pre', $category_id, $new_parent_id);

    $categories = db_get_hash_array(
        "SELECT `category_id`, `parent_id`, `id_path`, `level` FROM ?:categories WHERE `category_id` IN (?n)",
        'category_id',
        array($new_parent_id, $category_id)
    );
    if (empty($categories[$category_id])
        || (!empty($new_parent_id) && empty($categories[$new_parent_id]))
    ) {
        return false;
    }

    $category_modified = $categories[$category_id];
    if (!empty($new_parent_id) && !empty($categories[$new_parent_id])) {
        $category_modified['parent_id'] = $new_parent_id;
        $category_modified['level'] = ($categories[$new_parent_id]['level'] + 1);
        $category_modified['id_path'] = $categories[$new_parent_id]['id_path'] . '/' . $category_id;
    } else {
        $category_modified['parent_id'] = 0;
        $category_modified['level'] = 1;
        $category_modified['id_path'] = $category_id;
    }

    // Update category's tree position
    db_query(
        "UPDATE ?:categories SET `parent_id` = ?i, `id_path` = ?s, `level` = ?i WHERE `category_id` = ?i",
        $category_modified['parent_id'],
        $category_modified['id_path'],
        $category_modified['level'],
        $category_id
    );

    // Update existing category's children tree position
    if (isset($categories[$category_id]['parent_id']) && $categories[$category_id]['parent_id'] != $new_parent_id) {
        db_query(
            "UPDATE ?:categories
            SET
              `id_path` = CONCAT(?s, SUBSTRING(`id_path`, ?i)),
              `level` = `level` + ?i
            WHERE `id_path` LIKE ?l",
            $category_modified['id_path'] . "/",
            strlen($categories[$category_id]['id_path'] . '/') + 1,
            ((int)$category_modified['level'] - (int)$categories[$category_id]['level']),
            $categories[$category_id]['id_path'] . '/%'
        );

        /**
         * Adds additional actions after category parent updating
         *
         * @param int $category_id   Category identifier
         * @param int $new_parent_id Identifier of new category parent
         */
        fn_set_hook('update_category_parent_post', $category_id, $new_parent_id);
    }

    return true;
}

/**
 * Delete product option combination
 *
 * @param string $combination_hash Numeric Hash of options combination. (E.g. '3364473348')
 * @return bool Always true
 */
function fn_delete_product_combination($combination_hash)
{
    fn_delete_image_pairs($combination_hash, 'product_option');

    db_query("DELETE FROM ?:product_options_inventory WHERE combination_hash = ?i", $combination_hash);

    return true;
}

/**
 * Removes options and their variants by option identifier
 *
 * @param int $option_id Option identifier
 * @param int $pid Identifier of the product from which the option should be removed (for global options)
 * @return bool True on success, false otherwise
 */
function fn_delete_product_option($option_id, $pid = 0)
{
    /**
     * Adds additional actions before product option deleting
     *
     * @param int $option_id Option identifier
     * @param int $pid       Product identifier
     */
    fn_set_hook('delete_product_option_pre', $option_id, $pid);

    $option_deleted = false;

    if (!empty($option_id)) {
        $condition = fn_get_company_condition('?:product_options.company_id');
        $_otps = db_get_row("SELECT product_id, inventory FROM ?:product_options WHERE option_id = ?i $condition", $option_id);
        if (empty($_otps)) {
            return false;
        }

        $product_id = $_otps['product_id'];
        $option_inventory = $_otps['inventory'];
        $product_link = db_get_fields("SELECT product_id FROM ?:product_global_option_links WHERE option_id = ?i AND product_id = ?i", $option_id, $pid);
        if (empty($product_id) && !empty($product_link)) {
            // Linked option
            $option_description =  db_get_field("SELECT option_name FROM ?:product_options_descriptions WHERE option_id = ?i AND lang_code = ?s", $option_id, CART_LANGUAGE);
            db_query("DELETE FROM ?:product_global_option_links WHERE product_id = ?i AND option_id = ?i", $pid, $option_id);
            fn_set_notification('W', __('warning'), __('option_unlinked', array(
                '[option_name]' => $option_description
            )));
        } else {
            // Product option
            db_query("DELETE FROM ?:product_options_descriptions WHERE option_id = ?i", $option_id);
            db_query("DELETE FROM ?:product_options WHERE option_id = ?i", $option_id);
            fn_delete_product_option_variants($option_id);
        }

        if ($option_inventory == "Y" && !empty($product_id)) {
            fn_delete_product_option_combinations($product_id);
        }

        $option_deleted = true;
    }

    /**
     * Adds additional actions after product option deleting
     *
     * @param int  $option_id      Option identifier
     * @param int  $pid            Product identifier
     * @param bool $option_deleted True if option was successfully deleted, false otherwise
     */
    fn_set_hook('delete_product_option_post', $option_id, $pid, $option_deleted);

    return $option_deleted;
}

/**
 * Deletes product option combinations and its data (images). Used when deleting or changing product option.
 *
 * @param int $product_id Product Id.
 */
function fn_delete_product_option_combinations($product_id)
{
    /**
     * Adds additional actions before product option combinations deleting
     *
     * @param int $product_id Product Id.
     */
    fn_set_hook('delete_product_option_combinations', $product_id);

    if (!empty($product_id)) {
        $c_ids = db_get_fields("SELECT combination_hash FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
        db_query("DELETE FROM ?:product_options_inventory WHERE product_id = ?i", $product_id);
        foreach ($c_ids as $c_id) {
            fn_delete_image_pairs($c_id, 'product_option', '');
        }
    }
}

/**
 * Removes option variants
 *
 * @param int $option_id Option identifier: if given, all the option variants are deleted
 * @param int $variant_ids Variants identifiers: used if option_id is empty
 * @return bool Always true
 */
function fn_delete_product_option_variants($option_id = 0, $variant_ids = array())
{
    /**
     * Adds additional actions before product option variants deleting
     *
     * @param int $option_id   Option identifier: if given, all the option variants are deleted
     * @param int $variant_ids Variants identifiers: used if option_id is empty
     */
    fn_set_hook('delete_product_option_variants_pre', $option_id, $variant_ids);

    if (!empty($option_id)) {
        $_vars = db_get_fields("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i", $option_id);
    } elseif (!empty($variant_ids)) {
        $_vars = db_get_fields("SELECT variant_id FROM ?:product_option_variants WHERE variant_id IN (?n)", $variant_ids);
    }

    if (!empty($_vars)) {
        foreach ($_vars as $v_id) {
            db_query("DELETE FROM ?:product_option_variants_descriptions WHERE variant_id = ?i", $v_id);
            fn_delete_image_pairs($v_id, 'variant_image');
        }

        db_query("DELETE FROM ?:product_option_variants WHERE variant_id IN (?n)", $_vars);
    }

    /**
     * Adds additional actions after product option variants deleting
     *
     * @param int $option_id   Option identifier: if given, all the option variants are deleted
     * @param int $variant_ids Variants identifiers: used if option_id is empty
     */
    fn_set_hook('delete_product_option_variants_post', $option_id, $variant_ids);

    return true;
}

/**
 * Gets product options
 *
 * @param array $product_ids Product identifiers
 * @param string $lang_code 2-letters language code
 * @param bool $only_selectable Flag that forces to retreive the options with certain types (default: select, radio or checkbox)
 * @param bool $inventory Get only options with the inventory tracking
 * @param bool $only_avail Get only available options
 * @param bool $skip_global Get only general options, not global options, applied as link
 * @return array List of product options data
 */
function fn_get_product_options($product_ids, $lang_code = CART_LANGUAGE, $only_selectable = false, $inventory = false, $only_avail = false, $skip_global = false)
{
    $condition = $_status = $join = '';
    $extra_variant_fields = '';
    $option_ids = $variants_ids = $options = array();
    $selectable_option_types = array('S', 'R', 'C');

    /**
     * Get product options ( at the beggining of fn_get_product_options() )
     *
     * @param array  $product_ids             Product identifiers
     * @param string $lang_code               2-letters language code
     * @param bool   $only_selectable         This flag forces to retreive the options with the certain types (default: select, radio or checkbox)
     * @param bool   $inventory               Get only options with the inventory tracking
     * @param bool   $only_avail              Get only available options
     * @param array  $selectable_option_types Selectable option types
     * @param bool   $skip_global             Get only general options, not global options, applied as link
     */
    fn_set_hook('get_product_options_pre', $product_ids, $lang_code, $only_selectable, $inventory, $only_avail, $selectable_option_types, $skip_global);

    if (AREA == 'C' || $only_avail == true) {
        $_status .= " AND status = 'A'";
    }
    if ($only_selectable == true) {
        $condition .= db_quote(" AND a.option_type IN(?a)", $selectable_option_types);
    }
    if ($inventory == true) {
        $condition .= " AND a.inventory = 'Y'";
    }

    $join = db_quote(" LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id AND b.lang_code = ?s ", $lang_code);
    $fields = "a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message, b.comment";

    /**
     * Changes request params before product options selecting
     *
     * @param string $fields               Fields to be selected
     * @param string $condition            String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join                 String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $extra_variant_fields Additional variant fields to be selected
     * @param array  $product_ids          Product identifiers
     * @param string $lang_code            2-letters language code
     */
    fn_set_hook('get_product_options', $fields, $condition, $join, $extra_variant_fields, $product_ids, $lang_code);
    if (!empty($product_ids)) {
        $_options = db_get_hash_multi_array(
            "SELECT " . $fields
            . " FROM ?:product_options as a "
            . $join
            . " WHERE a.product_id IN (?n)" . $condition . $_status
            . " ORDER BY a.position",
            array('product_id', 'option_id'), $product_ids
        );
        if (!$skip_global) {
            $global_options = db_get_hash_multi_array(
                "SELECT c.product_id AS cur_product_id, a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message, b.comment"
                . " FROM ?:product_options as a"
                . " LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id AND b.lang_code = ?s"
                . " LEFT JOIN ?:product_global_option_links as c ON c.option_id = a.option_id"
                . " WHERE c.product_id IN (?n) AND a.product_id = 0" . $condition . $_status
                . " ORDER BY a.position",
                array('cur_product_id', 'option_id'), $lang_code, $product_ids
            );
        }
        foreach ((array) $product_ids as $product_id) {
            $_opts = (empty($_options[$product_id]) ? array() : $_options[$product_id]) + (empty($global_options[$product_id]) ? array() : $global_options[$product_id]);
            $options[$product_id] = fn_sort_array_by_key($_opts, 'position');
        }
    } else {
        //we need a separate query for global options
        $options = db_get_hash_multi_array(
            "SELECT a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message, b.comment"
            . " FROM ?:product_options as a"
            . $join
            . " WHERE a.product_id = 0" . $condition . $_status
            . " ORDER BY a.position",
            array('product_id', 'option_id')
        );
    }

    foreach ($options as $product_id => $_options) {
        $option_ids = array_merge($option_ids, array_keys($_options));
    }

    if (empty($option_ids)) {
        if (is_array($product_ids)) {
            return $options;
        } else {
            return !empty($options[$product_ids]) ? $options[$product_ids] : array();
        }
    }

    $_status = (AREA == 'A')? '' : " AND a.status='A'";

    $v_fields = "a.variant_id, a.option_id, a.position, a.modifier, a.modifier_type, a.weight_modifier, a.weight_modifier_type, $extra_variant_fields b.variant_name";
    $v_join = db_quote("LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id AND b.lang_code = ?s", $lang_code);
    $v_condition = db_quote("a.option_id IN (?n) $_status", array_unique($option_ids));
    $v_sorting = "a.position, a.variant_id";
    /**
     * Changes request params before product option variants selecting
     *
     * @param string $v_fields    Fields to be selected
     * @param string $v_condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $v_join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $v_sorting   String with the information for the "order by" statement
     * @param array  $option_ids  Options identifiers
     * @param string $lang_code   2-letters language code
     */
    fn_set_hook('get_product_options_get_variants', $v_fields, $v_condition, $v_join, $v_sorting, $option_ids, $lang_code);

    $variants = db_get_hash_multi_array("SELECT $v_fields FROM ?:product_option_variants as a $v_join WHERE $v_condition ORDER BY $v_sorting", array('option_id', 'variant_id'));

    foreach ($variants as $option_id => $_variants) {
        $variants_ids = array_merge($variants_ids, array_keys($_variants));
    }

    if (empty($variants_ids)) {
        return is_array($product_ids)? $options: $options[$product_ids];
    }

    $image_pairs = fn_get_image_pairs(array_unique($variants_ids), 'variant_image', 'V', true, true, $lang_code);

    foreach ($variants as $option_id => &$_variants) {
        foreach ($_variants as $variant_id => &$_variant) {
            $_variant['image_pair'] = !empty($image_pairs[$variant_id])? reset($image_pairs[$variant_id]) : array();
        }
    }

    foreach ($options as $product_id => &$_options) {
        foreach ($_options as $option_id => &$_option) {
            // Add variant names manually, if this option is "checkbox"
            if ($_option['option_type'] == 'C' && !empty($variants[$option_id])) {
                foreach ($variants[$option_id] as $variant_id => $variant) {
                    $variants[$option_id][$variant_id]['variant_name'] = $variant['position'] == 0 ? __('no') : __('yes');
                }
            }

            $_option['variants'] = !empty($variants[$option_id])? $variants[$option_id] : array();
        }
    }

    /**
     * Get product options ( at the end of fn_get_product_options() )
     *
     * @param array  $product_ids     Product ids
     * @param string $lang_code       Language code
     * @param bool   $only_selectable This flag forces to retreive the options with the certain types (default: select, radio or checkbox)
     * @param bool   $inventory       Get only options with the inventory tracking
     * @param bool   $only_avail      Get only available options
     * @param array  $options         The resulting array of the retrieved options
     */
    fn_set_hook('get_product_options_post', $product_ids, $lang_code, $only_selectable, $inventory, $only_avail, $options);

    return is_array($product_ids)? $options: $options[$product_ids];
}

/**
 * Returns a array of product options using some params
 *
 * @param array $params - array of params
 * @param int $items_per_page - items per page
 * @param $lang_code - language code
 * @return array ($product_options, $params, $product_options_count)
 */
function fn_get_product_global_options($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{

    /**
     * Changes params for getting product global options
     *
     * @param array  $params         Array of search params
     * @param int    $items_per_page Items per page
     * @param string $lang_code      2-letters language code
     */
    fn_set_hook('get_product_global_options_pre', $params, $items_per_page, $lang_code);

    $params = LastView::instance()->update('product_global_options', $params);

    $default_params = array(
        'product_id' => 0,
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $fields = array (
        '?:product_options.*',
        '?:product_options_descriptions.*',
    );

    $condition = $join = '';

    $join .= db_quote("LEFT JOIN ?:product_options_descriptions ON ?:product_options_descriptions.option_id = ?:product_options.option_id AND ?:product_options_descriptions.lang_code = ?s ", $lang_code);

    $order = 'ORDER BY position';

    $params['product_id'] = !empty($params['product_id']) ? $params['product_id'] : 0;
    $condition .= db_quote(" AND ?:product_options.product_id = ?i", $params['product_id']);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_options $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    /**
     * Changes SQL params before select product global options
     *
     * @param array  $params    Array of search params
     * @param array  $fields    Fields to be selected
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     */
    fn_set_hook('get_product_global_options_before_select', $params, $fields, $condition, $join);

    $data = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:product_options $join WHERE 1 $condition $order $limit ");

    /**
     * Changes product global options
     *
     * @param array $data   Product global options
     * @param array $params Array of search params
     */
    fn_set_hook('get_product_global_options_post', $data, $params);

    return array($data, $params);
}

/**
 * Returns an array of product options with values by combination
 *
 * @param string $combination Options combination code
 * @return array Options decoded from combination
 */

function fn_get_product_options_by_combination($combination)
{
    $options = array();

    /**
     * Changes product options (running before fn_get_product_options_by_combination function)
     *
     * @param string $combination Options combination code
     * @param array  $options     Array for options decoded from combination
     */
    fn_set_hook('get_product_options_by_combination_pre', $combination, $options);

    $_comb = explode('_', $combination);
    if (!empty($_comb) && is_array($_comb)) {
        $iterations = count($_comb);
        for ($i = 0; $i < $iterations; $i += 2) {
            $options[$_comb[$i]] = isset($_comb[$i + 1]) ? $_comb[$i + 1] : '';
        }
    }

    /**
     * Changes product options (running after fn_get_product_options_by_combination function)
     *
     * @param string $combination Options combination code
     * @param array  $options     options decoded from combination
     */
    fn_set_hook('get_product_options_by_combination_post', $combination, $options);

    return $options;
}

/**
 * Removes all product options from the product
 * @param int $product_id Product identifier
 */
function fn_poptions_delete_product($product_id)
{
    /**
     * Adds additional actions before delete all product option
     *
     * @param int $product_id Product identifier
     */
    fn_set_hook('poptions_delete_product_pre', $product_id);

    $option_ids = db_get_fields('SELECT option_id FROM ?:product_options WHERE product_id = ?i', $product_id);
    if (!empty($option_ids)) {
        foreach ($option_ids as $option_id) {
            fn_delete_product_option($option_id, $product_id);
        }
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $product_id);
    }

    db_query("DELETE FROM ?:product_global_option_links WHERE product_id = ?i", $product_id);

    $option_combinations = db_get_fields('SELECT combination_hash FROM ?:product_options_inventory WHERE product_id = ?i', $product_id);
    if (!empty($option_combinations)) {
        foreach ($option_combinations as $hash) {
            fn_delete_product_combination($hash);
        }
    }

    /**
     * Adds additional actions after delete all product option
     *
     * @param int $product_id Product identifier
     */
    fn_set_hook('poptions_delete_product_post', $product_id);
}

/**
 * Gets product options with the selected values data
 *
 * @param int $product_id Product identifier
 * @param array $selected_options Selected opotions values
 * @param string $lang_code 2-letters language code
 * @return array List of product options with selected values
 */
function fn_get_selected_product_options($product_id, $selected_options, $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params for selecting product options with selected values
     *
     * @param int    $product_id       Product identifier
     * @param array  $selected_options Selected opotions values
     * @param string $lang_code        2-letters language code
     */
    fn_set_hook('get_selected_product_options_pre', $product_id, $selected_options, $lang_code);

    $extra_variant_fields = '';
    $fields = db_quote("a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message, b.comment, a.status");
    $condition = db_quote("(a.product_id = ?i OR c.product_id = ?i) AND a.status = 'A'", $product_id, $product_id);
    $join = db_quote("LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id AND b.lang_code = ?s LEFT JOIN ?:product_global_option_links as c ON c.option_id = a.option_id", $lang_code);

    /**
     * Changes params before selecting product options
     *
     * @param string $fields               Fields to be selected
     * @param string $condition            String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join                 String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $extra_variant_fields Additional variant fields to be selected
     */
    fn_set_hook('get_selected_product_options_before_select', $fields, $condition, $join, $extra_variant_fields);

    $_opts = db_get_array("SELECT $fields FROM ?:product_options as a $join WHERE $condition ORDER BY a.position");
    if (is_array($_opts)) {
        $_status = (AREA == 'A') ? '' : " AND a.status = 'A'";
        foreach ($_opts as $k => $v) {
            $_vars = db_get_hash_array("SELECT a.variant_id, a.position, a.modifier, a.modifier_type, a.weight_modifier, a.weight_modifier_type, $extra_variant_fields  b.variant_name FROM ?:product_option_variants as a LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id AND b.lang_code = ?s WHERE a.option_id = ?i $_status ORDER BY a.position", 'variant_id', $lang_code, $v['option_id']);

            // Add variant names manually, if this option is "checkbox"
            if ($v['option_type'] == 'C' && !empty($_vars)) {
                foreach ($_vars as $variant_id => $variant) {
                    $_vars[$variant_id]['variant_name'] = $variant['position'] == 0 ? __('no') : __('yes');
                }
            }

            $_opts[$k]['value'] = (!empty($selected_options[$v['option_id']])) ? $selected_options[$v['option_id']] : '';
            $_opts[$k]['variants'] = $_vars;
        }

    }

    /**
     * Changes selected product options
     *
     * @param array  $_opts            Selected product options
     * @param int    $product_id       Product identifier
     * @param array  $selected_options Selected opotions values
     * @param string $lang_code        2-letters language code
     */
    fn_set_hook('get_selected_product_options_post', $_opts, $product_id, $selected_options, $lang_code);

    return $_opts;
}

/**
 * Calculates product price/weight with options modifiers
 *
 * @param array $product_options Product options
 * @param mixed $base_value Base price or weight value
 * @param string $type Calculation type (price or weight)
 * @param array $orig_options Original options
 * @param array $extra Extra data
 * @param mixed Recalculated value
 * @return mixed New base value after applying modifiers
 */
function fn_apply_options_modifiers($product_options, $base_value, $type, $orig_options = array(), $extra = array())
{
    static $option_types_cache = array();
    static $option_modifiers_cache = array();

    $fields = ($type == 'P') ? "a.modifier, a.modifier_type" : "a.weight_modifier as modifier, a.weight_modifier_type as modifier_type";

    /**
     * Apply option modifiers (at the beginning of the fn_apply_options_modifiers())
     *
     * @param array  $product_options Product options
     * @param mixed  $base_value      Base value
     * @param array  $orig_options    Original options
     * @param array  $extra           Extra data
     * @param string $fields          String of comma-separated SQL fields to be selected in an SQL-query
     * @param string $type            Calculation type (price or weight)
     */
    fn_set_hook('apply_option_modifiers_pre', $product_options, $base_value, $orig_options, $extra, $fields, $type);

    foreach ($orig_options as $key => $value) {
        // Exclude disabled (Forbidden) options
        if (empty($value['value'])) {
            unset($orig_options[$key]);
        }
    }

    $orig_value = $base_value;
    if (!empty($product_options)) {

        // Check options type. We need to apply only Selectbox, radiogroup and checkbox modifiers
        if (empty($orig_options)) {
            $_key = md5(serialize(array_keys($product_options)));
            if (!isset($option_types_cache[$_key])) {
                $option_types = db_get_hash_single_array("SELECT option_type as type, option_id FROM ?:product_options WHERE option_id IN (?n)", array('option_id', 'type'), array_keys($product_options));
                $option_types_cache[$_key] = $option_types;
            } else {
                $option_types = $option_types_cache[$_key];
            }
        } else {
            $option_types = array();
            foreach ($orig_options as $_opt) {
                $option_types[$_opt['option_id']] = $_opt['option_type'];
            }
        }

        foreach ($product_options as $option_id => $variant_id) {
            if (empty($option_types[$option_id]) || strpos('SRC', $option_types[$option_id]) === false) {
                continue;
            }
            if (empty($orig_options)) {
                $_key = md5($fields . $variant_id);
                if (!isset($option_modifiers_cache[$_key])) {
                    $om_join = "";
                    $om_condition = db_quote("a.variant_id = ?i", $variant_id);
                    /**
                    * Changes request params before option modifiers selecting
                    *
                    * @param string $type Calculation type (price or weight)
                    * @param string $fields Fields to be selected
                    * @param string $om_condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
                    * @param string $om_join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
                    * @param array $variant_id Variant identifier
                    */
                    fn_set_hook('apply_option_modifiers_get_option_modifiers', $type, $fields, $om_join, $om_condition, $variant_id);
                    $_mod = db_get_row("SELECT $fields FROM ?:product_option_variants a $om_join WHERE 1 AND $om_condition");
                    $option_modifiers_cache[$_key] = $_mod;
                } else {
                    $_mod = $option_modifiers_cache[$_key];
                }
            } else {
                foreach ($orig_options as $_opt) {
                    if ($_opt['value'] == $variant_id && !empty($variant_id)) {
                        $_mod = array();
                        $_mod['modifier'] = $_opt['modifier'];
                        $_mod['modifier_type'] = $_opt['modifier_type'];
                    }
                }
            }

            if (!empty($_mod)) {
                if ($_mod['modifier_type'] == 'A') {
                    // Absolute
                    if ($_mod['modifier']{0} == '-') {
                        $base_value = $base_value - floatval(substr($_mod['modifier'],1));
                    } else {
                        $base_value = $base_value + floatval($_mod['modifier']);
                    }
                } else {
                    // Percentage
                    if ($_mod['modifier']{0} == '-') {
                        $base_value = $base_value - ((floatval(substr($_mod['modifier'],1)) * $orig_value)/100);
                    } else {
                        $base_value = $base_value + ((floatval($_mod['modifier']) * $orig_value)/100);
                    }
                }
            }
        }
    }

    $base_value = ($base_value > 0) ? $base_value : 0;

    /**
     * Apply option modifiers (at the end of the fn_apply_options_modifiers())
     *
     * @param array  $product_options Product options
     * @param mixed  $base_value      Base value
     * @param string $type            Calculation type (price or weight)
     * @param array  $orig_options    Original options
     * @param mixed  $orig_value      Original base value
     * @param string $fields          String of comma-separated SQL fields to be selected in an SQL-query
     * @param array  $extra           Extra data
     */
    fn_set_hook('apply_option_modifiers_post', $product_options, $base_value, $type, $orig_options, $orig_value, $fields, $extra);

    return $base_value;
}

/**
 * Returns selected product options.
 * For options wich type is checkbox function gets translation from langvars 'no' and 'yes' and return it as variant_name.
 *
 * @param array  $selected_options Options as option_id => selected_variant_id.
 * @param string $lang_code        2digits language code.
 *
 * @return array Array of associative arrays wich contain options data.
 */
function fn_get_selected_product_options_info($selected_options, $lang_code = CART_LANGUAGE)
{
    /**
     * Get selected product options info (at the beginning of the fn_get_selected_product_options_info())
     *
     * @param array  $selected_options Selected options
     * @param string $lang_code        Language code
     */
    fn_set_hook('get_selected_product_options_info_pre', $selected_options, $lang_code);

    if (empty($selected_options) || !is_array($selected_options)) {
        return array();
    }
    $result = array();
    foreach ($selected_options as $option_id => $variant_id) {
        $_opts = db_get_row(
            "SELECT a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message " .
            "FROM ?:product_options as a LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id AND b.lang_code = ?s " .
            "WHERE a.option_id = ?i ORDER BY a.position",
            $lang_code, $option_id
        );

        if (empty($_opts)) {
            continue;
        }
        $_vars = array();
        if (strpos('SRC', $_opts['option_type']) !== false) {
            $_vars = db_get_row(
                "SELECT a.modifier, a.modifier_type, a.position, b.variant_name FROM ?:product_option_variants as a " .
                "LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id AND b.lang_code = ?s " .
                "WHERE a.variant_id = ?i ORDER BY a.position",
                $lang_code, $variant_id
            );
        }

        if ($_opts['option_type'] == 'C') {
            $_vars['variant_name'] = (empty($_vars['position'])) ? __('no', '', $lang_code) : __('yes', '', $lang_code);
        } elseif ($_opts['option_type'] == 'I' || $_opts['option_type'] == 'T') {
            $_vars['variant_name'] = $variant_id;
        } elseif (!isset($_vars['variant_name'])) {
            $_vars['variant_name'] = '';
        }

        $_vars['value'] = $variant_id;

        $result[] = fn_array_merge($_opts ,$_vars);
    }

    /**
     * Get selected product options info (at the end of the fn_get_selected_product_options_info())
     *
     * @param array  $selected_options Selected options
     * @param string $lang_code        Language code
     * @param array  $result           List of the option info arrays
     */
    fn_set_hook('get_selected_product_options_info_post', $selected_options, $lang_code, $result);

    return $result;
}

/**
 * Gets default product options
 *
 * @param integer $product_id Product identifier
 * @param bool $get_all Whether to get all the default options or not
 * @param array $product Product data
 * @return array The resulting array
 */
function fn_get_default_product_options($product_id, $get_all = false, $product = array())
{
    $result = $default = $exceptions = $product_options = array();
    $selectable_option_types = array('S', 'R', 'C');

    /**
    * Get default product options ( at the beginning of fn_get_default_product_options() )
    *
    * @param integer $product_id Product id
    * @param bool $get_all Whether to get all the default options or not
    * @param array $product Product data
    * @param array $selectable_option_types Selectable option types
    */
    fn_set_hook('get_default_product_options_pre', $product_id, $get_all, $product, $selectable_option_types);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $exceptions = fn_get_product_exceptions($product_id, true);
        $exceptions_type = (empty($product['exceptions_type']))? db_get_field('SELECT exceptions_type FROM ?:products WHERE product_id = ?i', $product_id) : $product['exceptions_type'];
    }

    $track_with_options = (empty($product['tracking']))? db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id) : $product['tracking'];

    if (!empty($product['product_options'])) {
        //filter out only selectable options
        foreach ($product['product_options'] as $option_id => $option) {
            if (in_array($option['option_type'], $selectable_option_types)) {
                $product_options[$option_id] = $option;
            }
        }
    } else {
        $product_options = fn_get_product_options($product_id, CART_LANGUAGE, true);
    }

    if (!empty($product_options)) {
        foreach ($product_options as $option_id => $option) {
            if (!empty($option['variants'])) {
                $default[$option_id] = key($option['variants']);
                foreach ($option['variants'] as $variant_id => $variant) {
                    $options[$option_id][$variant_id] = true;
                }
            }
        }
    } else {
        return array();
    }

    unset($product_options);
    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (empty($exceptions)) {
            return $default;
        }
    }

    $inventory_combinations = array();
    if ($track_with_options == ProductTracking::TRACK_WITH_OPTIONS) {
        $inventory_combinations = db_get_array("SELECT combination FROM ?:product_options_inventory WHERE product_id = ?i AND amount > 0 AND combination != ''", $product_id);
        if (!empty($inventory_combinations)) {
            $_combinations = array();
            foreach ($inventory_combinations as $_combination) {
                $_combinations[] = fn_get_product_options_by_combination($_combination['combination']);
            }
            $inventory_combinations = $_combinations;
            unset($_combinations);
        }
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($exceptions_type == 'F') {
            // Forbidden combinations
            $_options = array_keys($options);
            $_variants = array_values($options);
            if (!empty($_variants)) {
                foreach ($_variants as $key => $variants) {
                    $_variants[$key] = array_keys($variants);
                }
            }

            list($result) = fn_get_allowed_options_combination($_options, $_variants, '', 0, $exceptions, $inventory_combinations);

        } else {
            // Allowed combinations
            foreach ($exceptions as $exception) {
                $result = array();
                foreach ($exception as $option_id => $variant_id) {
                    if (isset($options[$option_id][$variant_id]) || $variant_id == -1) {
                        $result[$option_id] = ($variant_id != -1) ? $variant_id : (isset($options[$option_id]) ? key($options[$option_id]) : '');
                    } else {
                        continue 2;
                    }
                }

                $_opt = array_diff_key($options, $result);
                if (!empty($_opt)) {
                    foreach ($_opt as $option_id => $variants) {
                        $result[$option_id] = key($variants);
                    }
                }

                if (empty($inventory_combinations)) {
                    break;
                } else {
                    foreach ($inventory_combinations as $_icombination) {
                        $_res = array_diff($_icombination, $result);
                        if (empty($_res)) {
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
    * Get default product options ( at the end of fn_get_default_product_options() )
    *
    * @param integer $product_id Product id
    * @param bool $get_all Whether to get all the default options or not
    * @param array $product Product data
    * @param array $result The resulting array
    */
    fn_set_hook('get_default_product_options_post', $product_id, $get_all, $product, $result);

    return empty($result) ? $default : $result;
}

/**
 * Gets all possible options combinations
 *
 * @param array $options Options identifiers
 * @param array $variants Options variants identifiers in the order according to the $options parameter
 * @return array Combinations
 */
function fn_get_options_combinations($options, $variants)
{
    $combinations = array();

    // Take first option
    $options_key = array_keys($options);
    $variant_number = reset($options_key);
    $option_id = $options[$variant_number];

    // Remove current option
    unset($options[$variant_number]);

    // Get combinations for other options
    $sub_combinations = !empty($options) ? fn_get_options_combinations($options, $variants) : array();

    if (!empty($variants[$variant_number])) {
        // run through variants
        foreach ($variants[$variant_number] as $variant) {
            if (!empty($sub_combinations)) {
                // add current variant to each subcombination
                foreach ($sub_combinations as $sub_combination) {
                    $sub_combination[$option_id] = $variant;
                    $combinations[] = $sub_combination;
                }
            } else {
                $combinations[] = array(
                    $option_id => $variant
                );
            }
        }
    } else {
        $combinations = $sub_combinations;
    }

    return  $combinations;
}

/**
 * Generates product variants combinations
 *
 * @param int $product_id Product identifier
 * @param int $amount Default combination amount
 * @param array $options Array of option identifiers
 * @param array $variants Array of option variant identifier arrays in the order according to the $options parameter
 * @return array Array of combinations
 */
function fn_look_through_variants($product_id, $amount, $options, $variants)
{
    /**
     * Changes params for getting product variants combinations
     *
     * @param int   $product_id Product identifier
     * @param int   $amount     Default combination amount
     * @param array $options    Array of options identifiers
     * @param array $variants   Array of option variants identifiers arrays in order corresponding to $options parameter
     * @param array $string     Array of combinations values
     * @param int   $cycle      Options and variants key
     */
    fn_set_hook('look_through_variants_pre', $product_id, $amount, $options, $variants);

    $position = 0;
    $hashes = array();
    $combinations = fn_get_options_combinations($options, $variants);

    if (!empty($combinations)) {
        foreach ($combinations as $combination) {

            $_data = array();
            $_data['product_id'] = $product_id;

            $_data['combination_hash'] = fn_generate_cart_id($product_id, array('product_options' => $combination));

            if (array_search($_data['combination_hash'], $hashes) === false) {
                $hashes[] = $_data['combination_hash'];
                $_data['combination'] = fn_get_options_combination($combination);
                $_data['position'] = $position++;

                $old_data = db_get_row(
                    "SELECT combination_hash, amount, product_code "
                    . "FROM ?:product_options_inventory "
                    . "WHERE product_id = ?i AND combination_hash = ?i AND temp = 'Y'",
                    $product_id, $_data['combination_hash']
                );

                $_data['amount'] = isset($old_data['amount']) ? $old_data['amount'] : $amount;
                $_data['product_code'] = isset($old_data['product_code']) ? $old_data['product_code'] : '';

                /**
                 * Changes data before update combination
                 *
                 * @param array $combination Array of combination data
                 * @param array $data Combination data to update
                 * @param int $product_id Product identifier
                 * @param int $amount Default combination amount
                 * @param array $options Array of options identifiers
                 * @param array $variants Array of option variants identifiers arrays in order corresponding to $options parameter
                 */
                fn_set_hook('look_through_variants_update_combination', $combination, $_data, $product_id, $amount, $options, $variants);

                db_query("REPLACE INTO ?:product_options_inventory ?e", $_data);
                $combinations[] = $combination;
            }
            echo str_repeat('. ', count($combination));
        }
    }

    /**
     * Changes the product options combinations
     *
     * @param array $combination Array of combinations
     * @param int   $product_id  Product identifier
     * @param int   $amount      Default combination amount
     * @param array $options     Array of options identifiers
     * @param array $variants    Array of option variants identifiers arrays in order corresponding to $options parameter
     */
    fn_set_hook('look_through_variants_post', $combinations, $product_id, $amount, $options, $variants);

    return $combinations;
}

/**
 * Checks and rebuilds product options inventory if necessary
 *
 * @param int $product_id Product identifier
 * @param int $amount Default combination amount
 * @return boolean Always true
 */
function fn_rebuild_product_options_inventory($product_id, $amount = 50)
{

    /**
     * Changes parameters for rebuilding product options inventory
     * @param int $product_id Product identifier
     * @param int $amount     Default combination amount
     */
    fn_set_hook('rebuild_product_options_inventory_pre', $product_id, $amount);

    $_options = db_get_fields("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_global_option_links as b ON a.option_id = b.option_id WHERE (a.product_id = ?i OR b.product_id = ?i) AND a.option_type IN ('S','R','C') AND a.inventory = 'Y' ORDER BY position", $product_id, $product_id);
    if (empty($_options)) {
        return;
    }

    db_query("UPDATE ?:product_options_inventory SET temp = 'Y' WHERE product_id = ?i", $product_id);
    foreach ($_options as $k => $option_id) {
        $variants[$k] = db_get_fields("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i ORDER BY position", $option_id);
    }
    $combinations = fn_look_through_variants($product_id, $amount, $_options, $variants);

    // Delete image pairs assigned to old combinations
    $hashes = db_get_fields("SELECT combination_hash FROM ?:product_options_inventory WHERE product_id = ?i AND temp = 'Y'", $product_id);
    foreach ($hashes as $v) {
        fn_delete_image_pairs($v, 'product_option');
    }

    // Delete old combinations
    db_query("DELETE FROM ?:product_options_inventory WHERE product_id = ?i AND temp = 'Y'", $product_id);

    /**
     * Adds additional actions after rebuilding product options inventory
     *
     * @param int $product_id Product identifier
     */
    fn_set_hook('rebuild_product_options_inventory_post', $product_id);

    return true;
}

/**
 * Gets array of product features
 *
 * @param array $params Products features search params
 * @param int $items_per_page Items per page
 * @param string $lang_code 2-letters language code
 * @return array Array with 3 params
 *              array $data Products features data
 *              array $params Products features search params
 *              boolean $has_ungroupped Flag determines if there are features without group
 */
function fn_get_product_features($params = array(), $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params before getting products features
     *
     * @param array  $params         Products features search params
     * @param int    $items_per_page Items per page
     * @param string $lang_code      2-letters language code
     */
    fn_set_hook('get_product_features_pre', $params, $items_per_page, $lang_code);

    // Init filter
    $params = LastView::instance()->update('product_features', $params);

    $default_params = array(
        'product_id' => 0,
        'category_ids' => array(),
        'statuses' => AREA == 'C' ? array('A') : array(),
        'variants' => false,
        'plain' => false,
        'feature_types' => array(),
        'feature_id' => 0,
        'display_on' => '',
        'exclude_group' => false,
        'exclude_filters' => false,
        'page' => 1,
        'items_per_page' => $items_per_page,
        'variants_selected_only' => false
    );

    $params = array_merge($default_params, $params);

    $base_fields = $fields = array (
        'pf.feature_id',
        'pf.company_id',
        'pf.feature_type',
        'pf.parent_id',
        'pf.display_on_product',
        'pf.display_on_catalog',
        'pf.display_on_header',
        '?:product_features_descriptions.description',
        '?:product_features_descriptions.lang_code',
        '?:product_features_descriptions.prefix',
        '?:product_features_descriptions.suffix',
        'pf.categories_path',
        '?:product_features_descriptions.full_description',
        'pf.status',
        'pf.comparison',
        'pf.position'
    );

    $condition = $join = $group = '';
    $group_condition = '';

    $join .= db_quote(" LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = pf.feature_id AND ?:product_features_descriptions.lang_code = ?s", $lang_code);
    $join .= db_quote(" LEFT JOIN ?:product_features AS groups ON pf.parent_id = groups.feature_id");

    $fields[] = 'groups.position AS group_position';

    if (!empty($params['product_id'])) {
        $join .= db_quote(" LEFT JOIN ?:product_features_values ON ?:product_features_values.feature_id = pf.feature_id  AND ?:product_features_values.product_id = ?i AND ?:product_features_values.lang_code = ?s", $params['product_id'], $lang_code);

        if (!empty($params['existent_only'])) {
            $condition .= db_quote(" AND IF(pf.feature_type = 'G' OR pf.feature_type = 'C', 1, ?:product_features_values.feature_id)");
        }

        $fields[] = '?:product_features_values.value';
        $fields[] = '?:product_features_values.variant_id';
        $fields[] = '?:product_features_values.value_int';
    }

    if (!empty($params['feature_id'])) {
        $condition .= db_quote(" AND pf.feature_id = ?i", $params['feature_id']);
    }

    if (!empty($params['exclude_group'])) {
        $condition .= db_quote(" AND pf.feature_type != 'G'");
    }

    if (isset($params['description']) && fn_string_not_empty($params['description'])) {
        $condition .= db_quote(" AND ?:product_features_descriptions.description LIKE ?l", "%".trim($params['description'])."%");
    }

    if (!empty($params['statuses'])) {
        $condition .= db_quote(" AND pf.status IN (?a)", $params['statuses']);
    }

    if (isset($params['parent_id']) && $params['parent_id'] !== '') {
        $condition .= db_quote(" AND pf.parent_id = ?i", $params['parent_id']);
        $group_condition .= db_quote(" AND pf.feature_id = ?i", $params['parent_id']);
    }

    if (!empty($params['display_on']) && in_array($params['display_on'], array('product', 'catalog', 'header'))) {
        $condition .= " AND pf.display_on_$params[display_on] = 'Y'";
        $group_condition .= " AND pf.display_on_$params[display_on] = 'Y'";
    }

    if (!empty($params['feature_types'])) {
        $condition .= db_quote(" AND pf.feature_type IN (?a)", $params['feature_types']);
    }

    if (!empty($params['category_ids'])) {
        $c_ids = is_array($params['category_ids']) ? $params['category_ids'] : fn_explode(',', $params['category_ids']);
        $find_set = array(
            " pf.categories_path = '' "
        );

        if (!empty($params['search_in_subcats'])) {
            $child_ids = db_get_fields("SELECT a.category_id FROM ?:categories as a LEFT JOIN ?:categories as b ON b.category_id IN (?n) WHERE a.id_path LIKE CONCAT(b.id_path, '/%')", $c_ids);
            $c_ids = fn_array_merge($c_ids, $child_ids, false);
        }

        foreach ($c_ids as $k => $v) {
            $find_set[] = db_quote(" FIND_IN_SET(?i, pf.categories_path) ", $v);
        }

        $find_in_set = db_quote(" AND (?p)", implode('OR', $find_set));
        $condition .= $find_in_set;
        $group_condition .= $find_in_set;
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (!empty($params['exclude_filters'])) {
            $_condition = ' WHERE 1 ';

            if (fn_allowed_for('ULTIMATE')) {
                $_condition .= fn_get_company_condition('?:product_filters.company_id');
            }

            $exclude_feature_id = db_get_fields("SELECT ?:product_filters.feature_id FROM ?:product_filters $_condition GROUP BY ?:product_filters.feature_id");
            if (!empty($exclude_feature_id)) {
                $condition .= db_quote(" AND pf.feature_id NOT IN (?a)", $exclude_feature_id);
                unset($exclude_feature_id);
            }
        }
    }

    /**
     * Change SQL parameters before product features selection
     *
     * @param array  $fields    List of fields for retrieving
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     */
    fn_set_hook('get_product_features', $fields, $join, $condition, $params);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_features AS pf $join WHERE 1 $condition $group ORDER BY pf.position, ?:product_features_descriptions.description");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data = db_get_hash_array("SELECT " . implode(', ', $fields) . " FROM ?:product_features AS pf $join WHERE 1 $condition $group ORDER BY group_position, pf.position, ?:product_features_descriptions.description $limit", 'feature_id');
    $has_ungroupped = false;

    if (!empty($data)) {
        if ($params['variants'] == true) {
            foreach ($data as $k => $v) {
                if (in_array($v['feature_type'], array('S', 'M', 'N', 'E'))) {
                    if (AREA == 'A' && $v['feature_type'] != 'M' && empty($params['all_variants'])) {
                        $variants_per_page = PRODUCT_FEATURE_VARIANTS_THRESHOLD;
                    } else {
                        $variants_per_page = 0;
                    }

                    $_params = array(
                        'feature_id' => $v['feature_id'],
                        'product_id' => $params['product_id'],
                        'feature_type' => $v['feature_type'],
                        'get_images' => true,
                        'selected_only' => $params['variants_selected_only']
                        );

                    list($data[$k]['variants'], $_search) = fn_get_product_feature_variants($_params, $variants_per_page, $lang_code);
                    if (!empty($_search['total_items']) && $_search['total_items'] > PRODUCT_FEATURE_VARIANTS_THRESHOLD) {
                        $data[$k]['use_variant_picker'] = true;
                    }
                }
            }
        }
    }

    if ($params['plain'] == false) {
            // Get groups
        if (!empty($params['exclude_group'])) {
            $groups = db_get_hash_array("SELECT " . implode(', ', $base_fields) . " FROM ?:product_features AS pf LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = pf.feature_id AND ?:product_features_descriptions.lang_code = ?s WHERE (pf.feature_type = 'G') $group_condition ORDER BY pf.position, ?:product_features_descriptions.description", 'feature_id', $lang_code);

            foreach ($data as $k => $v) {
                if (empty($v['parent_id']) || empty($groups[$v['parent_id']])) {
                    $has_ungroupped = true;
                }
            }
            $data = fn_array_merge($data, $groups);
        }

        $delete_keys = array();
        foreach ($data as $k => $v) {
            if (!empty($v['parent_id']) && !empty($data[$v['parent_id']])) {
                $data[$v['parent_id']]['subfeatures'][$v['feature_id']] = $v;
                $data[$k] = & $data[$v['parent_id']]['subfeatures'][$v['feature_id']];
                $delete_keys[] = $k;
            }

            if (!empty($params['get_descriptions']) && empty($v['parent_id'])) {
                $d = fn_get_categories_list($v['categories_path']);
                $data[$k]['feature_description'] = __('display_on') . ': <span>' . implode(', ', $d) . '</span>';
            }
        }

        foreach ($delete_keys as $k) {
            unset($data[$k]);
        }
    }

    /**
     * Change products features data
     *
     * @param array   $data           Products features data
     * @param array   $params         Products features search params
     * @param boolean $has_ungroupped Flag determines if there are features without group
     */
    fn_set_hook('get_product_features_post', $data, $params, $has_ungroupped);

    return array($data, $params, $has_ungroupped);
}

/**
 * Gets single product feature data
 *
 * @param int $feature_id Feature identifier
 * @param boolean $get_variants Flag determines if product variants should be fetched
 * @param boolean $get_variant_images Flag determines if variant images should be fetched
 * @param string $lang_code 2-letters language code
 * @return array Product feature data
 */
function fn_get_product_feature_data($feature_id, $get_variants = false, $get_variant_images = false, $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params before getting product feature data
     *
     * @param int     $feature_id         Feature identifier
     * @param boolean $get_variants       Flag determines if product variants should be fetched
     * @param boolean $get_variant_images Flag determines if variant images should be fetched
     * @param string  $lang_code          2-letters language code
     */
    fn_set_hook('get_product_feature_data_pre', $feature_id, $get_variants, $get_variant_images, $lang_code);

    $fields = array(
        '?:product_features.feature_id',
        '?:product_features.feature_code',
        '?:product_features.company_id',
        '?:product_features.feature_type',
        '?:product_features.parent_id',
        '?:product_features.display_on_product',
        '?:product_features.display_on_catalog',
        '?:product_features.display_on_header',
        '?:product_features_descriptions.description',
        '?:product_features_descriptions.lang_code',
        '?:product_features_descriptions.prefix',
        '?:product_features_descriptions.suffix',
        '?:product_features.categories_path',
        '?:product_features_descriptions.full_description',
        '?:product_features.status',
        '?:product_features.comparison',
        '?:product_features.feature_type',
        '?:product_features.position'
    );

    $join = db_quote("LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id AND ?:product_features_descriptions.lang_code = ?s", $lang_code);

    $condition = db_quote("?:product_features.feature_id = ?i", $feature_id);

    /**
     * Change SQL parameters before fetching product feature data
     *
     * @param array   $fields             Array SQL fields to be selected in an SQL-query
     * @param string  $join               String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string  $condition          String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param int     $feature_id         Feature identifier
     * @param boolean $get_variants       Flag determines if product variants should be fetched
     * @param boolean $get_variant_images Flag determines if variant images should be fetched
     * @param string  $lang_code          2-letters language code
     */
    fn_set_hook('get_product_feature_data_before_select', $fields, $join, $condition, $feature_id, $get_variants, $get_variant_images, $lang_code);

    $feature_data = db_get_row("SELECT " . implode(",", $fields) . " FROM ?:product_features $join WHERE $condition");

    if ($get_variants == true && $feature_data) {
        list($feature_data['variants']) = fn_get_product_feature_variants(array(
            'feature_id' => $feature_id,
            'feature_type' => $feature_data['feature_type'],
            'get_images' => $get_variant_images
        ), 0, $lang_code);
    }

    /**
     * Change product feature data
     *
     * @param array $feature_data Product feature data
     */
    fn_set_hook('get_product_feature_data_post', $feature_data);

    return $feature_data;
}

/**
 * Gets product features list
 *
 * @param array $product Array with product data
 * @param string $display_on Code determines zone (product/catalog page) for that features are selected
 * @param string $lang_code 2-letters language code
 * @return array Product features
 */
function fn_get_product_features_list($product, $display_on = 'C', $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params before getting product features list
     *
     * @param array  $product    Array with product data
     * @param string $display_on Code determines zone (product/catalog page) for that features are selected
     * @param string $lang_code  2-letters language code
     */
    fn_set_hook('get_product_features_list_pre', $product, $display_on, $lang_code);

    $product_id = $product['product_id'];

    $features_list = array();

    if ($display_on == 'H') {
        $condition = " AND f.display_on_header = 'Y'";
    } elseif ($display_on == 'C') {
        $condition = " AND f.display_on_catalog = 'Y'";
    } elseif ($display_on == 'CP') {
        $condition = " AND (f.display_on_catalog = 'Y' OR f.display_on_product = 'Y')";
    } elseif ($display_on == 'A') {
        $condition = '';
    } else {
        $condition = " AND f.display_on_product = 'Y'";
    }

    $path = !empty($product['main_category']) ? explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $product['main_category'])) : array();

    if (fn_allowed_for('ULTIMATE')) {
        $product['shared_product'] = fn_ult_is_shared_product($product_id);
        if ($product['shared_product'] == 'Y') {
            //we should get features for all categories, not only main
            $path = !empty($product['category_ids']) ? explode('/', implode('/', db_get_fields("SELECT id_path FROM ?:categories WHERE category_id IN (?a)", $product['category_ids']))) : array();
        }
    }

    $find_set = array(
        " f.categories_path = '' "
    );
    foreach ($path as $k => $v) {
        $find_set[] = db_quote(" FIND_IN_SET(?i, f.categories_path) ", $v);
    }
    $find_in_set = db_quote(" AND (?p)", implode('OR', $find_set));
    $condition .= $find_in_set;

    $fields = db_quote("v.feature_id, v.value, v.value_int, v.variant_id, f.feature_type, fd.description, fd.prefix, fd.suffix, vd.variant, f.parent_id, ft.filter_id, ft.field_type, f.position, gf.position as gposition");
    $join = db_quote(
        "LEFT JOIN ?:product_features_values as v ON v.feature_id = f.feature_id "
        . " LEFT JOIN ?:product_features_descriptions as fd ON fd.feature_id = v.feature_id AND fd.lang_code = ?s"
        . " LEFT JOIN ?:product_feature_variants fv ON fv.variant_id = v.variant_id"
        . " LEFT JOIN ?:product_filters AS ft ON ft.feature_id = f.feature_id"
        . " LEFT JOIN ?:product_feature_variant_descriptions as vd ON vd.variant_id = fv.variant_id AND vd.lang_code = ?s"
        . " LEFT JOIN ?:product_features as gf ON gf.feature_id = f.parent_id AND gf.feature_type = ?s ",
        $lang_code, $lang_code, 'G');

    $condition = db_quote("f.status = 'A' AND IF(f.parent_id, (SELECT status FROM ?:product_features as df WHERE df.feature_id = f.parent_id), 'A') = 'A' AND v.product_id = ?i ?p AND (v.variant_id != 0 OR (f.feature_type != 'C' AND v.value != '') OR (f.feature_type = 'C') OR v.value_int != '') AND v.lang_code = ?s", $product_id, $condition, $lang_code);

    /**
     * Change SQL parameters before fetching product feature data
     *
     * @param string $fields    String of comma-separated SQL fields to be selected in an SQL-query
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param array  $product   Array with product data
     * @param string $lang_code 2-letters language code
     */
    fn_set_hook('get_product_features_list_before_select', $fields, $join, $condition, $product, $display_on, $lang_code);

    $_data = db_get_array("SELECT $fields FROM ?:product_features as f $join WHERE $condition ORDER BY fd.description, fv.position");

    if (!empty($_data)) {
        foreach ($_data as $k => $feature) {
            if ($feature['feature_type'] == 'C') {
                if ($feature['value'] != 'Y' && $display_on != 'A') {
                    unset($_data[$k]);
                    continue;
                }
            }

            if (empty($features_list[$feature['feature_id']])) {
                $features_list[$feature['feature_id']] = $feature;
            }

            if (!empty($feature['variant_id'])) { // feature has several variants
                if (!empty($feature['filter_id'])) {
                    $range_data = array(
                        'range_id' => $feature['variant_id'],
                        'range_name' => $feature['variant'],
                        'feature_type' => $feature['feature_type'],
                    );
                    $features_list[$feature['feature_id']]['features_hash'] = fn_add_range_to_url_hash('', $range_data, $feature['field_type']);
                }
                $features_list[$feature['feature_id']]['variants'][$feature['variant_id']] = array(
                    'value' => $feature['value'],
                    'value_int' => $feature['value_int'],
                    'variant_id' => $feature['variant_id'],
                    'variant' => $feature['variant']
                );
            }

            unset($features_list[$feature['feature_id']]['filter_id']);
            unset($features_list[$feature['feature_id']]['field_type']);
        }

    }

    $groups = array();
    foreach ($features_list as $f_id => $data) {
        $groups[$data['parent_id']]['features'][$f_id] = $data;
        $groups[$data['parent_id']]['position'] = empty($data['parent_id']) ? $data['position'] : $data['gposition'];
    }

    $features_list = array();
    if (!empty($groups)) {
        $groups = fn_sort_array_by_key($groups, 'position');
        foreach ($groups as $g) {
            $g['features'] = fn_sort_array_by_key($g['features'], 'position');
            $features_list = fn_array_merge($features_list, $g['features']);
        }
    }

    unset($groups);
    foreach ($features_list as $f_id => $data) {
        unset($features_list[$f_id]['position']);
        unset($features_list[$f_id]['gposition']);
    }

    /**
     * Changes product features list data
     *
     * @param array  $features_list Product features
     * @param array  $product       Array with product data
     * @param string $display_on    Code determines zone (product/catalog page) for that features are selected
     * @param string $lang_code     2-letters language code
     */
    fn_set_hook('get_product_features_list_post', $features_list, $product, $display_on, $lang_code);

    return $features_list;
}

/**
 * Gets products features
 *
 * @param string $lang_code 2-letters language code
 * @param boolean $simple Flag determines if only feature names(true) or all properties(false) should be selected
 * @param boolean $get_hidden Flag determines if all feature fields should be selected
 * @return array Product features
 */
function fn_get_avail_product_features($lang_code = CART_LANGUAGE, $simple = false, $get_hidden = true)
{
    /**
     * Changes parameters for getting available product features
     *
     * @param string  $lang_code  2-letters language code
     * @param boolean $simple     Flag determines if only feature names(true) or all properties(false) should be selected
     * @param boolean $get_hidden Flag determines if all feature fields should be selected
     */
    fn_set_hook('get_avail_product_features_pre', $lang_code,  $simple, $get_hidden);

    $statuses = array('A');

    if ($get_hidden == false) {
        $statuses[] = 'D';
    }

    if ($simple == true) {
        $fields = db_quote("?:product_features.feature_id, ?:product_features_descriptions.description");
    } else {
        $fields = db_quote("?:product_features.*, ?:product_features_descriptions.*");
    }

    $join = db_quote("LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id AND ?:product_features_descriptions.lang_code = ?s", $lang_code);

    $condition = db_quote("?:product_features.status IN (?a) AND ?:product_features.feature_type != 'G'", $statuses);

    /**
     * Change SQL parameters before fetching available product features
     *
     * @param string  $fields     String of comma-separated SQL fields to be selected in an SQL-query
     * @param string  $join       String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string  $condition  String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string  $lang_code  2-letters language code
     * @param boolean $simple     Flag determines if only feature names(true) or all properties(false) should be selected
     * @param boolean $get_hidden Flag determines if all feature fields should be selected
     */
    fn_set_hook('get_avail_product_features_before_select', $fields, $join, $condition, $lang_code,  $simple, $get_hidden);

    if ($simple == true) {
        $result = db_get_hash_single_array("SELECT $fields FROM ?:product_features $join WHERE $condition ORDER BY ?:product_features.position", array('feature_id', 'description'));
    } else {
        $result = db_get_hash_array("SELECT $fields FROM ?:product_features $join WHERE $condition ORDER BY ?:product_features.position", 'feature_id');
    }

    /**
     * Changes  available product features data
     *
     * @param array   $result     Product features
     * @param string  $lang_code  2-letters language code
     * @param boolean $simple     Flag determines if only feature names(true) or all properties(false) should be selected
     * @param boolean $get_hidden Flag determines if all feature fields should be selected
     */
    fn_set_hook('get_avail_product_features_post', $result, $lang_code,  $simple, $get_hidden);

    return $result;
}

/**
 * Gets product feature variants
 *
 * @param array $params array with search parameters
 * @param int $items_per_page Items per page
 * @param string $lang_code 2-letters language code
 * @return array Product feature variants
 */
function fn_get_product_feature_variants($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    /**
     * Changes parameters for getting product feature variants
     *
     * @param array  $params         array with search parameters
     * @param int    $items_per_page Items per page
     * @param string $lang_code      2-letters language code
     */
    fn_set_hook('get_product_feature_variants_pre', $params, $items_per_page, $lang_code);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'product_id' => 0,
        'feature_id' => 0,
        'feature_type' => '',
        'get_images' => false,
        'items_per_page' => $items_per_page,
        'selected_only' => false
    );

    $params = array_merge($default_params, $params);

    if (is_array($params['feature_id'])) {
        $fields = array(
            '?:product_feature_variant_descriptions.variant',
            '?:product_feature_variants.variant_id',
            '?:product_feature_variants.feature_id',
        );
    } else {
        $fields = array(
            '?:product_feature_variant_descriptions.*',
            '?:product_feature_variants.*',
        );
    }

    $condition = $group_by = $sorting = '';
    $feature_id = is_array($params['feature_id']) ? $params['feature_id'] : array($params['feature_id']);

    $join = db_quote(" LEFT JOIN ?:product_feature_variant_descriptions ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id AND ?:product_feature_variant_descriptions.lang_code = ?s", $lang_code);
    $condition .= db_quote(" AND ?:product_feature_variants.feature_id IN (?a)", $params['feature_id']);
    $sorting = db_quote("?:product_feature_variants.position, ?:product_feature_variant_descriptions.variant");

    if (!empty($params['product_id'])) {
        $fields[] = '?:product_features_values.variant_id as selected';
        $fields[] = '?:product_features.feature_type';

        if (!empty($params['selected_only'])) {
            $join .= db_quote(" INNER JOIN ?:product_features_values ON ?:product_features_values.variant_id = ?:product_feature_variants.variant_id AND ?:product_features_values.lang_code = ?s AND ?:product_features_values.product_id = ?i", $lang_code, $params['product_id']);
        } else {
            $join .= db_quote("  LEFT JOIN ?:product_features_values ON ?:product_features_values.variant_id = ?:product_feature_variants.variant_id AND ?:product_features_values.lang_code = ?s AND ?:product_features_values.product_id = ?i", $lang_code, $params['product_id']);
        }

        $join .= db_quote(" LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_feature_variants.feature_id");
        $group_by = db_quote(" GROUP BY ?:product_feature_variants.variant_id");
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_feature_variants WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    /**
     * Changes  SQL parameters for getting product feature variants
     *
     * @param array  $fields    List of fields for retrieving
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $group_by  String containing the SQL-query GROUP BY field
     * @param string $sorting   String containing the SQL-query ORDER BY clause
     * @param string $lang_code 2-letters language code
     * @param string $limit     String containing the SQL-query LIMIT clause
     */
    fn_set_hook('get_product_feature_variants', $fields, $join, $condition, $group_by, $sorting, $lang_code, $limit);

    $vars = db_get_hash_array('SELECT ' . implode(', ', $fields) . " FROM ?:product_feature_variants $join WHERE 1 $condition $group_by ORDER BY $sorting $limit", 'variant_id');

    if ($params['get_images'] == true && $params['feature_type'] == 'E') {
        $variant_ids = array();
        foreach ($vars as $variant) {
            $variant_ids[] = $variant['variant_id'];
        }
        $image_pairs = fn_get_image_pairs($variant_ids, 'feature_variant', 'V', true, true, $lang_code);
        foreach ($vars as &$variant) {
            $variant['image_pair'] = array_pop($image_pairs[$variant['variant_id']]);
        }
    }

    /**
     * Changes feature variants data
     *
     * @param array  $vars      Product feature variants
     * @param array  $params    array with search params
     * @param string $lang_code 2-letters language code
     */
    fn_set_hook('get_product_feature_variants_post', $vars, $params, $lang_code);

    return array($vars, $params);
}

/**
 * Gets product feature variant data
 *
 * @param int $variant_id Variant identifier
 * @param string $lang_code 2-letters language code
 * @return array Variant data
 */
function fn_get_product_feature_variant($variant_id, $lang_code = CART_LANGUAGE)
{
    $fields = "*";
    $join = db_quote("LEFT JOIN ?:product_feature_variant_descriptions ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id AND ?:product_feature_variant_descriptions.lang_code = ?s", $lang_code);
    $condition = db_quote("?:product_feature_variants.variant_id = ?i", $variant_id);

    /**
     * Changes SQL parameters before select product feature variant data
     *
     * @param string $fields     String of comma-separated SQL fields to be selected in an SQL-query
     * @param string $join       String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition  String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param int    $variant_id Variant identifier
     * @param string $lang_code  2-letters language code
     */
    fn_set_hook('get_product_feature_variant_before_select', $fields, $join, $condition, $variant_id, $lang_code);

    $var = db_get_row("SELECT $fields FROM ?:product_feature_variants $join WHERE $condition");

    if (empty($var)) {
        return false;
    }

    $var['image_pair'] = fn_get_image_pairs($variant_id, 'feature_variant', 'V', true, true, $lang_code);

    if (empty($var['meta_description']) && defined('AUTO_META_DESCRIPTION') && AREA != 'A') {
        $var['meta_description'] = fn_generate_meta_description($var['description']);
    }

    /**
     * Changes product feature variant data
     *
     * @param array  $var        Variant data
     * @param int    $feature_id Feature identifier
     * @param string $lang_code  2-letters language code
     */
    fn_set_hook('get_product_feature_variant_post', $var, $variant_id, $lang_code);

    return $var;
}

/**
 * Filters feature group data, leaves only settings that should be upllied to feature
 *
 * @param array $group_data Group data
 * @return array Filtered group data
 */
function fn_filter_feature_group_data($group_data)
{
    $display_settings = array('display_on_product', 'display_on_catalog', 'display_on_header');
    foreach ($display_settings as $setting) {
        if ($group_data[$setting] != 'Y') {
            unset($group_data[$setting]);
        }
    }

    return $group_data;
}

/**
 * Updates product feature
 *
 * @param array $feature_data Feature data
 * @param int $feature_id Feature identifier
 * @param string $lang_code 2-letters language code
 * @return int/boolean Feature identifier if product feature was updated, false otherwise
 */
function fn_update_product_feature($feature_data, $feature_id, $lang_code = DESCR_SL)
{

    /**
     * Changes before product feature updating
     *
     * @param array  $feature_data Feature data
     * @param int    $feature_id   Feature identifier
     * @param string $lang_code    2-letters language code
     */
    fn_set_hook('update_product_feature_pre', $feature_data, $feature_id, $lang_code);

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        if (!empty($feature_id) && $feature_id != NEW_FEATURE_GROUP_ID) {
            if (!fn_check_company_id('product_features', 'feature_id', $feature_id)) {
                fn_company_access_denied_notification();

                return false;
            }
            unset($feature_data['company_id']);
        }
    }

    $deleted_variants = array();
    $old_feature_data = array();

    // If this feature belongs to the group, get categories assignment from this group
    if (!empty($feature_data['parent_id'])) {
        $gdata = db_get_row("SELECT categories_path, display_on_product, display_on_catalog, display_on_header FROM ?:product_features WHERE feature_id = ?i", $feature_data['parent_id']);
        if (!empty($gdata)) {
            $gdata = fn_filter_feature_group_data($gdata);
            $feature_data = fn_array_merge($feature_data, $gdata);
        }
    }

    if (!intval($feature_id)) { // check for intval as we use "0G" for new group
        $feature_data['feature_id'] = $feature_id = db_query("INSERT INTO ?:product_features ?e", $feature_data);
        foreach (fn_get_translation_languages() as $feature_data['lang_code'] => $_d) {
            db_query("INSERT INTO ?:product_features_descriptions ?e", $feature_data);
        }
    } else {
        $old_feature_data = fn_get_product_feature_data($feature_id, false, false, DESCR_SL);

        $arow = db_query("UPDATE ?:product_features SET ?u WHERE feature_id = ?i", $feature_data, $feature_id);
        db_query('UPDATE ?:product_features_descriptions SET ?u WHERE feature_id = ?i AND lang_code = ?s', $feature_data, $feature_id, $lang_code);

        if (!$old_feature_data) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('feature'))),'','404');
            $feature_id = false;
        }
    }

    if ($feature_id) {

        // If this feature is group, set its categories to all children
        if ($feature_data['feature_type'] == 'G') {
            $u = array(
                'categories_path' => !empty($feature_data['categories_path']) ? $feature_data['categories_path'] : '',
                'display_on_product' => !empty($feature_data['display_on_product']) ? $feature_data['display_on_product'] : '',
                'display_on_catalog' => !empty($feature_data['display_on_catalog']) ? $feature_data['display_on_catalog'] : '',
                'display_on_header' => !empty($feature_data['display_on_header']) ? $feature_data['display_on_header'] : '',
            );
            $u = fn_filter_feature_group_data($u);
            db_query("UPDATE ?:product_features SET ?u WHERE parent_id = ?i", $u, $feature_id);
        }

        // Delete variants for simple features
        $old_categories = $old_feature_data ? fn_explode(',', $old_feature_data['categories_path']) : array();

        // Get sub-categories for OLD categories
        if (!empty($old_categories)) {
            $_condition = array();
            foreach ($old_categories as $category_id) {
                $_condition[] = db_quote('id_path LIKE ?l OR id_path LIKE ?l', $category_id . '/%', '%/' . $category_id . '/%');
            }

            $sub_cat_ids = db_get_fields('SELECT category_id FROM ?:categories WHERE ' . implode(' OR ', $_condition));
            $old_categories = array_merge($old_categories, $sub_cat_ids);
        }

        $new_categories = isset($feature_data['categories_path']) ? fn_explode(',', $feature_data['categories_path']) : array();

        // Get sub-categories for NEW categories
        if (!empty($new_categories)) {
            $_condition = array();
            foreach ($new_categories as $category_id) {
                $_condition[] = db_quote('id_path LIKE ?l OR id_path LIKE ?l', $category_id . '/%', '%/' . $category_id . '/%');
            }

            $sub_cat_ids = db_get_fields('SELECT category_id FROM ?:categories WHERE ' . implode(' OR ', $_condition));
            $new_categories = array_merge($new_categories, $sub_cat_ids);
        }

        if (($old_feature_data && $feature_data['feature_type'] != $old_feature_data['feature_type'])) {
            if (strpos('SMNE', $feature_data['feature_type']) === false) {
                fn_delete_product_feature_variants($feature_id);
            }
        }

        // Remove features values/variants if we changed categories list
        sort($old_categories);
        sort($new_categories);

        if (!fn_is_empty($new_categories) && (fn_is_empty($old_categories) || $old_categories != $new_categories)) {
            db_query('DELETE FROM ?:product_features_values WHERE feature_id = ?i AND product_id NOT IN (SELECT product_id FROM ?:products_categories WHERE link_type = ?s AND category_id IN (?a))', $feature_id, 'M', $new_categories);
        }

        if (strpos('SMNE', $feature_data['feature_type']) !== false) {
            fn_update_product_feature_variants($feature_id, $feature_data, $lang_code);
        }

        /**
         * Adds additional actions after product feature updating
         *
         * @param array  $feature_data     Feature data
         * @param int    $feature_id       Feature identifier
         * @param array  $deleted_variants Deleted product feature variants identifiers
         * @param string $lang_code        2-letters language code
         */
        fn_set_hook('update_product_feature_post', $feature_data, $feature_id, $deleted_variants, $lang_code);
    }

    return $feature_id;
}

/**
 * Updates product feature variants
 *
 * @param int $feature_id Feature identifier
 * @param array $feature_data Feature data
 * @param string $lang_code 2-letters language code
 *
 * @return array $variant_ids Feature variants identifier
 */
function fn_update_product_feature_variants($feature_id, &$feature_data, $lang_code = DESCR_SL)
{
    $variant_ids = array();

    if (!empty($feature_data['variants'])) {

        foreach ($feature_data['variants'] as $key => $variant) {
            $variant_id = fn_update_product_feature_variant($feature_id, $feature_data['feature_type'], $variant);

            $variant_ids[$key] = $variant_id;
            $feature_data['variants'][$key]['variant_id'] = $variant_id; // for addons
        }

        if (!empty($variant_ids)) {
            fn_attach_image_pairs('variant_image', 'feature_variant', 0, $lang_code, $variant_ids);
        }

        if (!empty($feature_data['original_var_ids'])) {
            $original_variant_ids = explode(',', $feature_data['original_var_ids']);
            $deleted_variants = array_diff($original_variant_ids, $variant_ids);

            fn_delete_product_feature_variants(0, $deleted_variants);
        }
    }

}

/**
 * Updates product feature variant
 *
 * @param int $feature_id Feature identifier
 * @param array $feature_type Feature type
 * @param array $variant Feature variant data
 * @param string $lang_code 2-letters language code
 *
 * @return array $variant_id Feature variant identifier
 */
function fn_update_product_feature_variant($feature_id, $feature_type, $variant, $lang_code = DESCR_SL)
{
    if (empty($variant['variant']) && (!isset($variant['variant']) || $variant['variant'] !== '0')) {
        return false;
    }

    $variant['feature_id'] = $feature_id;

    if (isset($variant['variant_id'])) {
        $variant_id = db_get_field('SELECT variant_id FROM ?:product_feature_variants WHERE variant_id = ?i', $variant['variant_id']);
        unset($variant['variant_id']);
    }

    if (empty($variant_id)) {
        $join = db_quote('INNER JOIN ?:product_feature_variants fv ON fv.variant_id = fvd.variant_id');
        $variant_id = db_get_field("SELECT fvd.variant_id FROM ?:product_feature_variant_descriptions AS fvd $join WHERE variant = ?s AND feature_id = ?i", $variant['variant'], $feature_id);
    }

    if (empty($variant_id)) {
        $variant_id = $variant['variant_id'] = db_query("INSERT INTO ?:product_feature_variants ?e", $variant);
        foreach (Languages::getAll() as $variant['lang_code'] => $_v) {
            db_query("INSERT INTO ?:product_feature_variant_descriptions ?e", $variant);
        }
    } else {
        db_query("UPDATE ?:product_feature_variants SET ?u WHERE variant_id = ?i", $variant, $variant_id);
        db_query("UPDATE ?:product_feature_variant_descriptions SET ?u WHERE variant_id = ?i AND lang_code = ?s", $variant, $variant_id, $lang_code);
    }

    if ($feature_type == 'N') { // number
        db_query('UPDATE ?:product_features_values SET ?u WHERE variant_id = ?i AND lang_code = ?s', array('value_int' => $variant['variant']), $variant_id, $lang_code);
    }

    return $variant_id;
}

/**
 * Removes product feature
 *
 * @param int $feature_id Feature identifier
 * @return boolean Always true
 */
function fn_delete_feature($feature_id)
{
    $feature_deleted = true;

    if (fn_allowed_for('ULTIMATE')) {
        if (!fn_check_company_id('product_features', 'feature_id', $feature_id)) {
            fn_company_access_denied_notification();

            return false;
        }
    }

    /**
     * Adds additional actions before product feature deleting
     *
     * @param int $feature_id Feature identifier
     */
    fn_set_hook('delete_feature_pre', $feature_id);

    $feature_type = db_get_field("SELECT feature_type FROM ?:product_features WHERE feature_id = ?i", $feature_id);

    fn_set_hook('delete_product_feature', $feature_id, $feature_type);

    if ($feature_type == 'G') {
        $fids = db_get_fields("SELECT feature_id FROM ?:product_features WHERE parent_id = ?i", $feature_id);
        if (!empty($fids)) {
            foreach ($fids as $fid) {
                fn_delete_feature($fid);
            }
        }
    }

    $affected_rows = db_query("DELETE FROM ?:product_features WHERE feature_id = ?i", $feature_id);
    db_query("DELETE FROM ?:product_features_descriptions WHERE feature_id = ?i", $feature_id);

    if ($affected_rows == 0) {
        fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('feature'))),'','404');
        $feature_deleted = false;
    }

    $variant_ids = fn_delete_product_feature_variants($feature_id);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $filter_ids = db_get_fields("SELECT filter_id FROM ?:product_filters WHERE feature_id = ?i", $feature_id);
        foreach ($filter_ids as $_filter_id) {
            fn_delete_product_filter($_filter_id);
        }
    }

    /**
     * Adds additional actions after product feature deleting
     *
     * @param int   $feature_id  Deleted feature identifier
     * @param array $variant_ids Deleted feature variants
     */
    fn_set_hook('delete_feature_post', $feature_id, $variant_ids);

    return $feature_deleted;
}

/**
 * Removes feature variants
 *
 * @param int $feature_id Feature identifier
 * @param array $variant_ids Variants identifier
 * @return array $variant_ids Deleted feature variants
 */
function fn_delete_product_feature_variants($feature_id = 0, $variant_ids = array())
{
    if (!empty($feature_id)) {
        $variant_ids = db_get_fields("SELECT variant_id FROM ?:product_feature_variants WHERE feature_id = ?i", $feature_id);
        db_query("DELETE FROM ?:product_features_values WHERE feature_id = ?i", $feature_id);
        db_query("DELETE FROM ?:product_feature_variants WHERE feature_id = ?i", $feature_id);

    } elseif (!empty($variant_ids)) {
        db_query("DELETE FROM ?:product_feature_variants WHERE variant_id IN (?n)", $variant_ids);
        db_query("DELETE FROM ?:product_features_values WHERE variant_id IN (?n)", $variant_ids);
    }

    if (!empty($variant_ids)) {
        db_query("DELETE FROM ?:product_feature_variant_descriptions WHERE variant_id IN (?n)", $variant_ids);
        foreach ($variant_ids as $variant_id) {
            fn_delete_image_pairs($variant_id, 'feature_variant');
        }
    }

    return $variant_ids;
}

/**
 * Gets product filter name
 *
 * @param array $filter_id Filter identifier
 * @param string $lang_code 2 letters language code
 * @return string|bool Filter name on success, false otherwise
 */
function fn_get_product_filter_name($filter_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($filter_id)) {
        if (is_array($filter_id)) {
            return db_get_hash_single_array("SELECT filter_id, filter FROM ?:product_filter_descriptions WHERE filter_id IN (?n) AND lang_code = ?s", array('filter_id', 'filter'), $filter_id, $lang_code);
        } else {
            return db_get_field("SELECT filter FROM ?:product_filter_descriptions WHERE filter_id = ?i AND lang_code = ?s", $filter_id, $lang_code);
        }
    }

    return false;
}

/**
 * Gets product filters by search params
 *
 * @param array $params Products filter search params
 * @param int $items_per_page Items per page
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array Product filters
 */
function fn_get_product_filters($params = array(), $items_per_page = 0, $lang_code = DESCR_SL)
{
    /**
     * Changes product filters search params
     *
     * @param array  $params         Products filter search params
     * @param int    $items_per_page Items per page
     * @param string $lang_code      2-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_product_filters_pre', $params, $items_per_page, $lang_code);

    // Init filter
    $params = LastView::instance()->update('product_filters', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $condition = $group = '';

    if (!empty($params['filter_id'])) {
        $condition .= db_quote(" AND ?:product_filters.filter_id IN (?n)", (array) $params['filter_id']);
    }

    if (isset($params['filter_name']) && fn_string_not_empty($params['filter_name'])) {
        $condition .= db_quote(" AND ?:product_filter_descriptions.filter LIKE ?l", "%".trim($params['filter_name'])."%");
    }

    if (!empty($params['show_on_home_page'])) {
        $condition .= db_quote(" AND ?:product_filters.show_on_home_page = ?s", $params['show_on_home_page']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:product_filters.status = ?s", $params['status']);
    }

    if (isset($params['feature_name']) && fn_string_not_empty($params['feature_name'])) {
        $condition .= db_quote(" AND ?:product_features_descriptions.description LIKE ?l", "%".trim($params['feature_name'])."%");
    }

    if (!empty($params['category_ids'])) {
        $c_ids = is_array($params['category_ids']) ? $params['category_ids'] : fn_explode(',', $params['category_ids']);
        $find_set = array(
            " ?:product_filters.categories_path = '' "
        );
        foreach ($c_ids as $k => $v) {
            $find_set[] = db_quote(" FIND_IN_SET(?i, ?:product_filters.categories_path) ", $v);
        }
        $find_in_set = db_quote(" AND (?p)", implode('OR', $find_set));
        $condition .= $find_in_set;
    }

    if (fn_allowed_for('ULTIMATE')) {
        $condition .= fn_get_company_condition('?:product_filters.company_id');
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_filters LEFT JOIN ?:product_filter_descriptions ON ?:product_filter_descriptions.lang_code = ?s AND ?:product_filter_descriptions.filter_id = ?:product_filters.filter_id LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_filters.feature_id AND ?:product_features_descriptions.lang_code = ?s LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id WHERE 1 ?p", $lang_code, $lang_code, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $fields = "";
    if (!empty($params['short'])) {
        $fields .= db_quote("?:product_filters.filter_id, ?:product_filters.feature_id, ?:product_filters.field_type, ?:product_filters.status, ");
        if (fn_allowed_for('ULTIMATE')) {
            $fields .= db_quote("?:product_filters.company_id, ");
        }
    } else {
        $fields .= db_quote("?:product_filters.*, ?:product_features_descriptions.description as feature, ");
    }

    $fields .= db_quote("?:product_filter_descriptions.filter, ?:product_features.feature_type, ?:product_features.parent_id, ?:product_features_descriptions.prefix, ?:product_features_descriptions.suffix");
    $join = db_quote("LEFT JOIN ?:product_filter_descriptions ON ?:product_filter_descriptions.lang_code = ?s AND ?:product_filter_descriptions.filter_id = ?:product_filters.filter_id LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_filters.feature_id AND ?:product_features_descriptions.lang_code = ?s LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id", $lang_code, $lang_code);
    $sorting = db_quote("?:product_filters.position, ?:product_filter_descriptions.filter");
    $group_by = db_quote("GROUP BY ?:product_filters.filter_id");

    /**
     * Changes SQL parameters for product filters select
     *
     * @param string $fields    String of comma-separated SQL fields to be selected in an SQL-query
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $group_by  String containing the SQL-query GROUP BY field
     * @param string $sorting   String containing the SQL-query ORDER BY clause
     * @param string $limit     String containing the SQL-query LIMIT clause
     * @param array  $params    Products filter search params
     * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_product_filters_before_select', $fields, $join, $condition, $group_by, $sorting, $limit, $params, $lang_code);

    $filters = db_get_hash_array("SELECT $fields FROM ?:product_filters $join WHERE 1 ?p $group_by ORDER BY $sorting $limit", 'filter_id', $condition);

    if (!empty($filters)) {
        $fields = fn_get_product_filter_fields();

        // Get feature group if exist
        $parent_ids = array();
        foreach ($filters as $k => $v) {
            if (!empty($v['parent_id'])) {
                $parent_ids[] = $v['parent_id'];
            }
        }
        $groups = db_get_hash_array("SELECT feature_id, description FROM ?:product_features_descriptions WHERE feature_id IN (?n) AND lang_code = ?s", 'feature_id', $parent_ids, $lang_code);

        foreach ($filters as $k => $filter) {

            if (!empty($filter['parent_id']) && !empty($groups[$filter['parent_id']])) {
                $filters[$k]['feature_group'] = $groups[$filter['parent_id']]['description'];
            }

            if (!empty($filter['field_type'])) {
                $filters[$k]['feature'] = __($fields[$filter['field_type']]['description']);
            }
            if (empty($filter['feature_id'])) {
                $filters[$k]['condition_type'] = $fields[$filter['field_type']]['condition_type'];
            }

            if (!empty($fields[$filter['field_type']]['slider'])) {
                $filters[$k]['slider'] = $fields[$filter['field_type']]['slider'];
            }

            if (!empty($params['get_descriptions'])) {
                $d = array();
                $filters[$k]['filter_description'] = __('filter_by') . ': <span>' . $filters[$k]['feature'] . (!empty($filters[$k]['feature_group']) ? ' (' . $filters[$k]['feature_group'] . ' )' : '') . '</span>';

                if ($filter['show_on_home_page'] == 'Y') {
                    $d[] = __('home_page');
                }

                $d = fn_array_merge($d, fn_get_categories_list($filter['categories_path'], $lang_code), false);
                $filters[$k]['filter_description'] .= ' | ' . __('display_on') . ': <span>' . implode(', ', $d) . '</span>';
            }

            $_ids[$filter['filter_id']] = $filter['feature_type'] != 'N' ? $filter['feature_id'] : null;
        }

        if (!empty($params['get_variants'])) {

            $ranges = db_get_hash_array("SELECT ?:product_filter_ranges.*, ?:product_filter_ranges_descriptions.range_name FROM ?:product_filter_ranges LEFT JOIN ?:product_filter_ranges_descriptions ON ?:product_filter_ranges_descriptions.range_id = ?:product_filter_ranges.range_id AND ?:product_filter_ranges_descriptions.lang_code = ?s WHERE filter_id IN (?a) ORDER BY position", 'range_id', $lang_code, array_keys($_ids));

            if (!empty($ranges)) {
                foreach ($ranges as $range_id => $range) {
                    unset($_ids[$range['filter_id']]);
                    $filters[$range['filter_id']]['ranges'][$range_id] = $range;
                }
            }
            unset($ranges);

            if (!empty($_ids)) {

                list($variants) = fn_get_product_feature_variants(array(
                    'feature_id' => array_values($_ids)
                ));
                $_ids_revert = array();
                foreach ($_ids as $filter_id => $feature_id) {
                    if (!empty($feature_id)) {
                        $_ids_revert[$feature_id][] = $filter_id;
                    }
                }

                foreach ($variants as $variant_id => $variant) {
                    if (!empty($_ids_revert[$variant['feature_id']])) {
                        foreach ($_ids_revert[$variant['feature_id']] as $filter_id) {
                            if (!empty($params['short'])) {
                                $filters[$filter_id]['ranges'][$variant_id] = array('variant_id' => $variant['variant_id'], 'variant' => $variant['variant']);
                            } else {
                                $filters[$filter_id]['ranges'][$variant_id] = $variant;
                            }
                        }
                    }
                    unset($variants[$variant_id]);
                }

                unset($variants);
            }
            unset($_ids);
        }
    }

    /**
     * Changes product filters data
     *
     * @param array  $filters   Product filters
     * @param array  $params    Products filter search params
     * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_product_filters_post', $filters, $params, $lang_code);

    return array($filters, $params);
}

function fn_correct_features_hash($features_hash)
{
    list($av_ids, $ranges_ids, $_field_ranges_ids, $slider_vals, $field_ranges_ids) = fn_parse_features_hash($features_hash);

    $fields = fn_get_product_filter_fields();

    if (!empty($field_ranges_ids)) {
        foreach ($field_ranges_ids as $field_type => $rids) {
            $structure = $fields[$field_type];
            if ($structure['condition_type'] == 'C') {
                if (count($rids) > 1 ) {
                    foreach ($rids as $rid) {
                        $features_hash = fn_delete_range_from_url($features_hash, array('range_id' => $rid), $field_type);
                    }
                }
            }
        }
    }

    return $features_hash;
}

/**
 * Gets product filters with ranges
 *
 * @param array $params Products filter search params
 * @return array Products and filters data
 *               array $filters - Product filters data
 *               array $view_all - All ranges filters
 */
function fn_get_filters_products_count($params = array())
{
    /**
     * Change parameters for getting product filters count
     *
     * @param array $params Products filter search params
     */
    fn_set_hook('get_filters_products_count_pre', $params);

    $key = 'pfilters_' . md5(serialize($params));

    Registry::registerCache($key, array('products', 'product_features', 'product_filters', 'product_features_values', 'categories'), Registry::cacheLevel('user'));

    if (Registry::isExist($key) == false) {
        if (!empty($params['check_location'])) { // FIXME: this is bad style, should be refactored
            $valid_locations = array(
                'index.index',
                'products.search',
                'categories.view',
                'product_features.view'
            );

            if (!in_array($params['dispatch'], $valid_locations)) {
                return array();
            }

            if ($params['dispatch'] == 'categories.view') {
                $params['simple_link'] = true; // this parameter means that extended filters on this page should be displayed as simple
                $params['filter_custom_advanced'] = true; // this parameter means that extended filtering should be stayed on the same page
            } else {
                if ($params['dispatch'] == 'product_features.view') {
                    $params['simple_link'] = true;
                    $params['features_hash'] = (!empty($params['features_hash']) ? ($params['features_hash'] . '.') : '') . 'V' . $params['variant_id'];
                    //$params['exclude_feature_id'] = db_get_field("SELECT feature_id FROM ?:product_features_values WHERE variant_id = ?i", $params['variant_id']);
                }

                $params['get_for_home'] = 'Y';
            }
        }

        // hide filters block on the advanced search page
        if (!empty($params['skip_if_advanced']) && !empty($params['advanced_filter']) && $params['advanced_filter'] == 'Y') {
            return array();
        }

        // Base fields for the SELECT queries
        $values_fields = array (
            '?:product_features_values.feature_id',
            'COUNT(DISTINCT ?:products.product_id) as products',
            '?:product_features_values.variant_id as range_id',
            '?:product_feature_variant_descriptions.variant as range_name',
            '?:product_features.feature_type',
            '?:product_filters.filter_id'
        );

        $ranges_fields = array (
            '?:product_features_values.feature_id',
            'COUNT(DISTINCT ?:products.product_id) as products',
            '?:product_filter_ranges.range_id',
            '?:product_filter_ranges_descriptions.range_name',
            '?:product_filter_ranges.filter_id',
            '?:product_features.feature_type'
        );

        $condition = $where = $join = $filter_vq = $filter_rq = '';

        $advanced_variant_ids = $ranges_ids = $field_filters = $feature_ids = $field_ranges_ids = $field_ranges_counts = $field_range_values = $slider_vals = array();

        if (!empty($params['features_hash']) && empty($params['skip_advanced_variants'])) {
            list($av_ids, $ranges_ids, $_field_ranges_ids, $slider_vals, $field_ranges_ids) = fn_parse_features_hash($params['features_hash']);
            $advanced_variant_ids = db_get_hash_multi_array("SELECT feature_id, variant_id FROM ?:product_feature_variants WHERE variant_id IN (?n)", array('feature_id', 'variant_id'), $av_ids);
        }

        if (!empty($params['category_id'])) {
            if (Registry::get('settings.General.show_products_from_subcategories') == 'Y') {
                $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $params['category_id']);
                $category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE id_path LIKE ?l", $id_path . '/%');
            } else {
                $category_ids = array();
            }
            $category_ids[] = $params['category_id'];

            $condition .= db_quote(" AND (categories_path = '' OR FIND_IN_SET(?i, categories_path))", $params['category_id']);

            $where .= db_quote(" AND ?:products_categories.category_id IN (?n)", $category_ids);
        } elseif (empty($params['get_for_home']) && empty($params['get_custom'])) {
            $condition .= " AND categories_path = ''";
        }

        if (!empty($params['filter_id'])) {
            $condition .= db_quote(" AND ?:product_filters.filter_id = ?i", $params['filter_id']);
        }

        if (!empty($params['item_ids'])) {
            $condition .= db_quote(" AND ?:product_filters.filter_id IN (?a)", explode(',', $params['item_ids']));
        }

        if (!empty($params['get_for_home'])) {
            $condition .= db_quote(" AND ?:product_filters.show_on_home_page = ?s", $params['get_for_home']);
        }

        if (!empty($params['exclude_feature_id'])) {
            $condition .= db_quote(" AND ?:product_filters.feature_id NOT IN (?n)", $params['exclude_feature_id']);
        }

        if (fn_allowed_for('ULTIMATE')) {
            $condition .= fn_get_company_condition('?:product_filters.company_id');
        }

        $sf_fields = db_quote("?:product_filters.feature_id, ?:product_filters.filter_id, ?:product_filters.field_type, ?:product_filters.round_to, ?:product_filters.display, ?:product_filters.display_count, ?:product_filters.display_more_count, ?:product_filter_descriptions.filter, ?:product_features_descriptions.prefix, ?:product_features_descriptions.suffix");
        $sf_join =  db_quote("LEFT JOIN ?:product_filter_descriptions ON ?:product_filter_descriptions.filter_id = ?:product_filters.filter_id AND ?:product_filter_descriptions.lang_code = ?s LEFT JOIN ?:product_features_descriptions ON ?:product_features_descriptions.feature_id = ?:product_filters.feature_id AND ?:product_features_descriptions.lang_code = ?s", CART_LANGUAGE, CART_LANGUAGE);
        $sf_sorting = db_quote("position, filter");

        /**
         * Change SQL parameters before select product filters
         *
         * @param array $sf_fields String of comma-separated SQL fields to be selected in an SQL-query
         * @param string $sf_join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
         * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
         * @param string $sf_sorting String containing the SQL-query ORDER BY clause
         * @param array $params Products filter search params
         */
        fn_set_hook('get_filters_products_count_before_select_filters', $sf_fields, $sf_join, $condition, $sf_sorting, $params);

        $limit = '';
        if (fn_allowed_for('ULTIMATE:FREE')) {
            $limit = db_quote(' LIMIT ?i', FILTERS_LIMIT);
        }

        $filters = db_get_hash_array("SELECT $sf_fields FROM ?:product_filters $sf_join WHERE ?:product_filters.status = 'A' ?p ORDER BY $sf_sorting $limit", 'filter_id', $condition);

        $fields = fn_get_product_filter_fields();

        if (empty($filters) && empty($params['advanced_filter'])) {
            return array(array(), false);
        } else {
            foreach ($filters as $k => $v) {
                if (!empty($v['feature_id'])) {
                    // Feature filters
                    $feature_ids[] = $v['feature_id'];
                } else {
                    // Product field filters
                    if (!empty($fields[$v['field_type']])) {
                        $_field = $fields[$v['field_type']];
                        $field_filters[$v['filter_id']] = array_merge($v, $_field);
                        $filters[$k]['condition_type'] = $_field['condition_type'];
                        if (!empty($_field['slider'])) {
                            $filters[$k]['slider'] = $_field['slider'];
                        }
                    }
                }
            }
        }
        // Variants
        if (!empty($advanced_variant_ids)) {
            $join .= db_quote(" LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS advanced_variants FROM ?:product_features_values WHERE lang_code = ?s GROUP BY product_id) AS pfv_advanced ON pfv_advanced.product_id = ?:products.product_id", CART_LANGUAGE);

            $where_and_conditions = array();
            foreach ($advanced_variant_ids as $k => $variant_ids) {
                $where_or_conditions = array();
                foreach ($variant_ids as $variant_id => $v) {
                    $where_or_conditions[] = db_quote(" FIND_IN_SET('?i', advanced_variants)", $variant_id);
                }
                $where_and_conditions[] = '(' . implode(' OR ', $where_or_conditions) . ')';
            }
            $where .= ' AND ' . implode(' AND ', $where_and_conditions);
        }
        // Ranges
        if (!empty($ranges_ids)) {
            $filter_conditions = db_get_hash_multi_array("SELECT `from`, `to`, feature_id, filter_id, range_id FROM ?:product_filter_ranges WHERE range_id IN (?n)", array('filter_id', 'range_id'), $ranges_ids);
            $where_conditions = array();
            foreach ($filter_conditions as $fid => $range_conditions) {
                foreach ($range_conditions as $k => $condition) {
                    $k = $fid . "_" . $k;
                    $join .= db_quote(" LEFT JOIN ?:product_features_values as var_val_$k ON var_val_$k.product_id = ?:products.product_id AND var_val_$k.lang_code = ?s", CART_LANGUAGE);
                    $where_conditions[] = db_quote("(var_val_$k.value_int >= ?i AND var_val_$k.value_int <= ?i AND var_val_$k.value = '' AND var_val_$k.feature_id = ?i)", $condition['from'], $condition['to'], $condition['feature_id']);
                }
                $where .= db_quote(" AND (?p)", implode(" OR ", $where_conditions));
                $where_conditions = array();
            }
        }

        if (!empty($params['filter_id']) && empty($params['view_all'])) {
            $filter_vq .= db_quote(" AND ?:product_filters.filter_id = ?i", $params['filter_id']);
            $filter_rq .= db_quote(" AND ?:product_filter_ranges.filter_id = ?i", $params['filter_id']);
        }

        if (!empty($params['view_all'])) {
            $values_fields[] = "UPPER(SUBSTRING(?:product_feature_variant_descriptions.variant, 1, 1)) AS `index`";
        }

        $_join = $join;

        // Build condition for the standart fields
        if (!empty($field_ranges_ids)) {
            foreach ($field_ranges_ids as $field_type => $rids) {
                $structure = $fields[$field_type];

                if (empty($fields[$field_type])) {
                    continue;
                }

                if ($structure['table'] !== 'products' && strpos($join, 'JOIN ?:' . $structure['table']) === false) {
                    $join .= " LEFT JOIN ?:$structure[table] ON ?:$structure[table].product_id = ?:products.product_id";
                }

                if ($structure['condition_type'] == 'D' && empty($structure['slider'])) {
                    foreach ($rids as $rid) {
                        $range_condition = db_get_row("SELECT `from`, `to` FROM ?:product_filter_ranges WHERE range_id = ?i", $rid);
                        if (!empty($range_condition)) {
                            $where .= db_quote(" AND ?:$structure[table].$structure[db_field] >= ?i AND ?:$structure[table].$structure[db_field] <= ?i", $range_condition['from'], $range_condition['to']);
                        }
                    }
                } elseif ($structure['condition_type'] == 'F') {
                    $where .= db_quote(" AND ?:$structure[table].$structure[db_field] IN (?a)", $rids);
                } elseif ($structure['condition_type'] == 'C') {
                    if (count($rids) > 1 ) {
                        unset($field_ranges_ids[$field_type]);
                        foreach ($rids as $rid) {
                            $params['features_hash'] = fn_delete_range_from_url($params['features_hash'], array('range_id' => $rid), $field_type);
                        }
                    } else {
                        $where .= db_quote(" AND ?:$structure[table].$structure[db_field] = ?s", ($rids[0] == 1) ? 'Y' : 'N');
                    }
                }
                if (!empty($structure['join_params'])) {
                    foreach ($structure['join_params'] as $field => $param) {
                        $join .= db_quote(" AND ?:$structure[table].$field = ?s ", $param);
                    }
                }
            }
        }

        // Product availability conditions
        $where .= ' AND (' . fn_find_array_in_set($_SESSION['auth']['usergroup_ids'], '?:categories.usergroup_ids', true) . ')';
        $where .= ' AND (' . fn_find_array_in_set($_SESSION['auth']['usergroup_ids'], '?:products.usergroup_ids', true) . ')';
        $where .= db_quote(" AND ?:categories.status IN (?a) AND ?:products.status IN (?a)", array('A', 'H'), array('A'));

        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $categories_join_condition = db_quote(' AND ?:categories.company_id = ?i', Registry::get('runtime.company_id'));
        } else {
            $categories_join_condition = '';
        }

        $_j = " INNER JOIN ?:products_categories ON ?:products_categories.product_id = ?:products.product_id LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id $categories_join_condition";

        if (fn_allowed_for('MULTIVENDOR') && AREA == 'C') {
            if (!empty($_REQUEST['company_id'])) {
                $where .= " AND (?:products.company_id = " . !empty($_REQUEST['company_id']) . ") ";
            }
            $where .= " AND (companies.status = 'A')";
            $_j .= " LEFT JOIN ?:companies as companies ON companies.company_id = ?:products.company_id";
        }

        $_join .= $_j;
        $join .= $_j;

        $inventory_join = '';
        if (Registry::get('settings.General.inventory_tracking') == 'Y' &&
            Registry::get('settings.General.show_out_of_stock_products') == 'N' &&
            AREA == 'C'
        ) {
            $inventory_join .= " LEFT JOIN ?:product_options_inventory as inventory ON inventory.product_id = ?:products.product_id";
            $where .= db_quote(
                " AND (CASE ?:products.tracking
                    WHEN ?s THEN inventory.amount > 0
                    WHEN ?s THEN ?:products.amount > 0
                    WHEN ?s THEN 1
                END)",
                ProductTracking::TRACK_WITH_OPTIONS,
                ProductTracking::TRACK_WITHOUT_OPTIONS,
                ProductTracking::DO_NOT_TRACK
            );
        }

        $join .= $inventory_join;

        // Localization
        $where .= fn_get_localizations_condition('?:products.localization', true);
        $where .= fn_get_localizations_condition('?:categories.localization', true);

        $sliders_join = $sliders_where = '';

        /**
         * Change SQL parameters before select filter variants and products count
         *
         * @param array $values_fields Array of SQL fields to be selected in an SQL-query
         * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
         * @param string $sliders_join String with the additional complete JOIN information (JOIN type, tables and fields) for an SQL-query (for slider range filters)
         * @param array $feature_ids Array of feature IDs.
         * @param string $where String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
         * @param string $sliders_where String containing additional SQL-query condition possibly prepended with a logical operator (AND or OR) (for slider range filters)
         * @param string $filter_vq String containing additional SQL-query condition for filter with variants possibly prepended with a logical operator (AND or OR) (for slider range filters)
         * @param string $filter_rq String containing additional SQL-query condition for filter with ranges possibly prepended with a logical operator (AND or OR) (for slider range filters)
         */
        fn_set_hook('get_filters_products_count_query_params', $values_fields, $join, $sliders_join, $feature_ids, $where, $sliders_where, $filter_vq, $filter_rq);

        if (!empty($field_filters)) {
            // Field ranges

            foreach ($field_filters as $filter_id => $field) {

                $fields_join = $fields_where = '';

                // Dinamic ranges (price, amount etc)
                if ($field['condition_type'] == 'D') {

                    $_fields_join = " LEFT JOIN ?:$field[table] ON ?:$field[table].$field[db_field] >= ?:product_filter_ranges.from AND ?:$field[table].$field[db_field] <= ?:product_filter_ranges.to ";

                    if ($field['field_type'] != 'A') {
                        if (strpos($_join, 'JOIN ?:products ') === false) {
                            $fields_join .= " LEFT JOIN ?:products ON ?:products.product_id = ?:product_prices.product_id";
                        } elseif (strpos($fields_join . $_join, 'JOIN ?:product_prices ') === false) {
                            $fields_join .= db_quote(" LEFT JOIN ?:product_prices ON ?:product_prices.product_id = ?:products.product_id AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n)", array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));
                        }
                    }

                    if ($field['table'] == 'product_prices') {
                        $fields_join .= db_quote(" LEFT JOIN ?:product_prices as prices_2 ON ?:product_prices.product_id = prices_2.product_id AND ?:product_prices.price > prices_2.price AND prices_2.lower_limit = 1 AND prices_2.usergroup_id IN (?n)", array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));
                        $fields_where .= db_quote(" AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n)", array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));
                        $fields_where .= " AND prices_2.price IS NULL";
                    }

                    if (empty($field['slider'])) {
                        $fields_join = $_fields_join . $fields_join . $inventory_join;
                        $field_ranges_counts[$filter_id] = db_get_hash_array("SELECT COUNT(DISTINCT ?:$field[table].product_id) as products, ?:product_filter_ranges.range_id, ?:product_filter_ranges_descriptions.range_name, ?:product_filter_ranges.filter_id FROM ?:product_filter_ranges LEFT JOIN ?:product_filter_ranges_descriptions ON ?:product_filter_ranges_descriptions.range_id = ?:product_filter_ranges.range_id AND ?:product_filter_ranges_descriptions.lang_code = ?s ?p WHERE ?:products.status IN ('A') AND ?:product_filter_ranges.filter_id = ?i ?p GROUP BY ?:product_filter_ranges.range_id HAVING products != 0 ORDER BY ?:product_filter_ranges.position, ?:product_filter_ranges_descriptions.range_name", 'range_id', CART_LANGUAGE, $fields_join . $_join, $filter_id, $where . $fields_where);
                    } else {

                        if ($field['field_type'] == 'A') {
                            $db_field = db_quote(
                                "IF(?:products.tracking = ?s, inventory.amount, ?:products.amount)",
                                ProductTracking::TRACK_WITH_OPTIONS
                            );
                            $fields_join .= " LEFT JOIN ?:product_options_inventory as inventory ON inventory.product_id = ?:products.product_id";
                        } else {
                            $db_field = "?:$field[table].$field[db_field]";
                            $fields_join .= $inventory_join;

                            if (fn_allowed_for('ULTIMATE') && $field['field_type'] == 'P' && Registry::get('runtime.company_id')) {
                                $db_field = "IF(shared_prices.product_id IS NOT NULL, shared_prices.price, ?:product_prices.price)";
                                $fields_join .= db_quote(" LEFT JOIN ?:ult_product_prices AS shared_prices ON shared_prices.product_id = ?:products.product_id"
                                    . " AND shared_prices.lower_limit = 1"
                                    . " AND shared_prices.usergroup_id IN (?n)"
                                    . " AND shared_prices.company_id = ?i",
                                    array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']),
                                    Registry::get('runtime.company_id')
                                );
                            }
                        }

                        $field_range_values[$filter_id] = db_get_row("SELECT MIN($db_field) min, MAX($db_field) max FROM ?:$field[table] ?p WHERE ?:products.status IN ('A') ?p", $fields_join . $_join, $where . $fields_where);

                        if (fn_is_empty($field_range_values[$filter_id])) {
                            unset($field_range_values[$filter_id]);
                        } else {

                            if ($field['field_type'] == 'P' && CART_SECONDARY_CURRENCY != CART_PRIMARY_CURRENCY) {
                                $coef = Registry::get('currencies.' . CART_SECONDARY_CURRENCY . '.coefficient');
                                $field_range_values[$filter_id]['min'] = floatval($field_range_values[$filter_id]['min']) / floatval($coef);
                                $field_range_values[$filter_id]['max'] = floatval($field_range_values[$filter_id]['max']) / floatval($coef);
                            }

                            $field_range_values[$filter_id]['min'] = floor($field_range_values[$filter_id]['min'] / $filters[$filter_id]['round_to']) * $filters[$filter_id]['round_to'];
                            $field_range_values[$filter_id]['max'] = ceil($field_range_values[$filter_id]['max'] / $filters[$filter_id]['round_to']) * $filters[$filter_id]['round_to'];

                            if ($field_range_values[$filter_id]['max'] - $field_range_values[$filter_id]['min'] <= $filters[$filter_id]['round_to']) {
                                $field_range_values[$filter_id]['max'] = $field_range_values[$filter_id]['min'] + $filters[$filter_id]['round_to'];
                            }

                            if (!empty($slider_vals[$field['field_type']])) {
                                $_slider_vals[$field['field_type']] = $slider_vals[$field['field_type']];
                                if ($field['field_type'] == 'P' && $slider_vals['P'][2] != CART_SECONDARY_CURRENCY) {
                                    $prev_coef = Registry::get('currencies.' . $slider_vals['P'][2] . '.coefficient');
                                    $cur_coef  = Registry::get('currencies.' . CART_SECONDARY_CURRENCY . '.coefficient');
                                    $slider_vals['P'][0] = floor(floatval($slider_vals['P'][0]) * floatval($prev_coef) / floatval($cur_coef));
                                    $slider_vals['P'][1] = ceil(floatval($slider_vals['P'][1]) * floatval($prev_coef) / floatval($cur_coef));
                                }

                                $field_range_values[$filter_id]['left'] = $slider_vals[$field['field_type']][0];
                                $field_range_values[$filter_id]['right'] = $slider_vals[$field['field_type']][1];

                                if ($field_range_values[$filter_id]['left'] < $field_range_values[$filter_id]['min']) {
                                    $field_range_values[$filter_id]['left'] = $field_range_values[$filter_id]['min'];
                                }
                                if ($field_range_values[$filter_id]['left'] > $field_range_values[$filter_id]['max']) {
                                    $field_range_values[$filter_id]['left'] = $field_range_values[$filter_id]['max'];
                                }
                                if ($field_range_values[$filter_id]['right'] > $field_range_values[$filter_id]['max']) {
                                    $field_range_values[$filter_id]['right'] = $field_range_values[$filter_id]['max'];
                                }
                                if ($field_range_values[$filter_id]['right'] < $field_range_values[$filter_id]['min']) {
                                    $field_range_values[$filter_id]['right'] = $field_range_values[$filter_id]['min'];
                                }
                                if ($field_range_values[$filter_id]['right'] < $field_range_values[$filter_id]['left']) {
                                    $tmp = $field_range_values[$filter_id]['right'];
                                    $field_range_values[$filter_id]['right'] = $field_range_values[$filter_id]['left'];
                                    $field_range_values[$filter_id]['left'] = $tmp;
                                }

                                $field_range_values[$filter_id]['left'] = floor($field_range_values[$filter_id]['left'] / $filters[$filter_id]['round_to']) * $filters[$filter_id]['round_to'];
                                $field_range_values[$filter_id]['right'] = ceil($field_range_values[$filter_id]['right'] / $filters[$filter_id]['round_to']) * $filters[$filter_id]['round_to'];

                                /*if ($field_range_values[$filter_id]['right'] - $field_range_values[$filter_id]['left'] <= $filters[$filter_id]['round_to']) {
                                    $field_range_values[$filter_id]['right'] = $field_range_values[$filter_id]['left'] + $filters[$filter_id]['round_to'];
                                }*/

                                if (!empty($field_range_values[$filter_id]['left']) || !empty($field_range_values[$filter_id]['right'])) {
                                    if ($field['field_type'] == 'P') {
                                        if (strpos($sliders_join, 'JOIN ?:product_prices ') === false) {
                                            if (strpos($join, 'JOIN ?:product_prices ') === false) {
                                                $sliders_join .= db_quote(" LEFT JOIN ?:product_prices ON ?:product_prices.product_id = ?:products.product_id AND ?:product_prices.lower_limit = 1 AND ?:product_prices.usergroup_id IN (?n)", array_merge(array(USERGROUP_ALL), $_SESSION['auth']['usergroup_ids']));
                                            }
                                            $vals = $_slider_vals['P'];
                                            $currency = !empty($vals[2]) ? $vals[2] : CART_PRIMARY_CURRENCY;
                                            if ($currency != CART_PRIMARY_CURRENCY) {
                                                $coef = Registry::get('currencies.' . $currency . '.coefficient');
                                                $decimals = Registry::get('currencies.' . CART_PRIMARY_CURRENCY . '.decimals');
                                                $vals[0] = round(floatval($vals[0]) * floatval($coef), $decimals);
                                                $vals[1] = round(floatval($vals[1]) * floatval($coef), $decimals);
                                            }

                                            $sliders_where .= db_quote(" AND ?:product_prices.price >= ?i AND ?:product_prices.price <= ?i", $vals[0], $vals[1]);
                                        }
                                    } elseif ($field['field_type'] == 'A') {
                                        if (strpos($sliders_join, 'JOIN ?:product_options_inventory ') === false) {
                                            if (strpos($join, 'JOIN ?:product_options_inventory ') === false) {
                                                $sliders_join .= " LEFT JOIN ?:product_options_inventory as inventory ON inventory.product_id = ?:products.product_id";
                                            }
                                            $sliders_where .= db_quote(" AND $db_field >= ?i AND $db_field <= ?i", $field_range_values[$filter_id]['left'], $field_range_values[$filter_id]['right']);
                                        }
                                    }
                                }
                            }
                        }
                    }

                // Char values (free shipping etc)
                } elseif ($field['condition_type'] == 'C') {
                    $field_ranges_counts[$filter_id] = db_get_hash_array("SELECT COUNT(DISTINCT ?:$field[table].product_id) as products, ?:$field[table].$field[db_field] as range_name, ?s AS field_type FROM ?:$field[table] ?p WHERE ?:products.status = 'A' ?p GROUP BY ?:$field[table].$field[db_field]", 'range_name', $field['field_type'], $join, $where);
                    if (!empty($field_ranges_counts[$filter_id])) {
                        foreach ($field_ranges_counts[$filter_id] as $range_key => $range) {
                            $field_ranges_counts[$filter_id][$range_key]['range_name'] = $field['variant_descriptions'][$range['range_name']];
                            $field_ranges_counts[$filter_id][$range_key]['range_id'] = ($range['range_name'] == 'Y') ? 1 : 0;
                        }
                    }
                // Fixed values (supplier etc)
                } elseif ($field['condition_type'] == 'F') {
                    $field_ranges_counts[$filter_id] = db_get_hash_array("SELECT COUNT(DISTINCT ?:$field[table].product_id) as products, ?:$field[foreign_table].$field[range_name] as range_name, UPPER(SUBSTRING(?:$field[foreign_table].$field[range_name], 1, 1)) AS `index`, ?:$field[foreign_table].$field[foreign_index] as range_id, ?s AS field_type FROM ?:$field[table] LEFT JOIN ?:$field[foreign_table] ON ?:$field[foreign_table].$field[foreign_index] = ?:$field[table].$field[db_field] ?p WHERE ?:products.status IN ('A') ?p GROUP BY ?:$field[table].$field[db_field] ORDER BY ?:$field[foreign_table].$field[range_name] ", 'range_id', $field['field_type'], $join, $where);
                }
            }
        }

        $filter_company_condition = "";
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $filters_list = db_get_fields("SELECT ?:product_filters.filter_id FROM ?:product_filters");
            if (!empty($filters_list)) {
                $filter_company_condition = db_quote(" AND ?:product_filters.filter_id IN (?a)", $filters_list);
            }
        }

        if (!fn_allowed_for('ULTIMATE') || !empty($filters_list)) {
            $variants_counts = db_get_hash_multi_array("SELECT " . implode(', ', $values_fields) . " FROM ?:product_features_values LEFT JOIN ?:products ON ?:products.product_id = ?:product_features_values.product_id LEFT JOIN ?:product_filters ON ?:product_filters.feature_id = ?:product_features_values.feature_id AND ?:product_filters.status = 'A' LEFT JOIN ?:product_feature_variants ON ?:product_feature_variants.variant_id = ?:product_features_values.variant_id LEFT JOIN ?:product_feature_variant_descriptions ON ?:product_feature_variant_descriptions.variant_id = ?:product_feature_variants.variant_id AND ?:product_feature_variant_descriptions.lang_code = ?s LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filters.feature_id ?p WHERE ?:product_features_values.feature_id IN (?n) AND ?:product_features_values.lang_code = ?s AND ?:product_features_values.variant_id ?p ?p AND ?:product_features.feature_type IN ('S', 'M', 'E') GROUP BY ?:product_features_values.variant_id, ?:product_filters.filter_id ORDER BY ?:product_feature_variants.position, ?:product_feature_variant_descriptions.variant", array('filter_id', 'range_id'), CART_LANGUAGE, $join . $sliders_join, $feature_ids, CART_LANGUAGE, $where . $sliders_where . $filter_company_condition, $filter_vq);
        } else {
            $variants_counts = array();
        }

        $ranges_counts = db_get_hash_multi_array("SELECT " . implode(', ', $ranges_fields) . " FROM ?:product_filter_ranges LEFT JOIN ?:product_features_values ON ?:product_features_values.feature_id = ?:product_filter_ranges.feature_id AND ?:product_features_values.value_int >= ?:product_filter_ranges.from AND ?:product_features_values.value_int <= ?:product_filter_ranges.to LEFT JOIN ?:products ON ?:products.product_id = ?:product_features_values.product_id LEFT JOIN ?:product_filter_ranges_descriptions ON ?:product_filter_ranges_descriptions.range_id = ?:product_filter_ranges.range_id AND ?:product_filter_ranges_descriptions.lang_code = ?s LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_filter_ranges.feature_id ?p WHERE ?:product_features_values.feature_id IN (?n) AND ?:product_features_values.lang_code = ?s ?p ?p GROUP BY ?:product_filter_ranges.range_id ORDER BY ?:product_filter_ranges.position, ?:product_filter_ranges_descriptions.range_name", array('filter_id', 'range_id'), CART_LANGUAGE, $join . $sliders_join, $feature_ids, CART_LANGUAGE, $where . $sliders_where, $filter_rq);

        $merged = fn_array_merge($variants_counts, $ranges_counts, $field_ranges_counts);

        $view_all = array();

        if (empty($merged) && empty($params['skip_other_variants']) && (!empty($av_ids) || !empty($ranges_ids) || !empty($_field_ranges_ids))) {
            fn_set_notification('W', __('text_nothing_found'), __('text_nothing_found_filter_message'));

            if (defined('AJAX_REQUEST')) {
                exit;
            } elseif (!empty($_SERVER['HTTP_REFERER'])) {
                fn_redirect($_SERVER['HTTP_REFERER']);
            } else {
                $_params = $params;
                $_params['skip_advanced_variants'] = true;
                $_params['only_selected'] = true;

                list($_f, $_view_all) = fn_get_filters_products_count($_params);
                foreach ($_f as $filter_id => $filter) {
                    if (!empty($field_range_values[$filter_id])) {
                        $_f[$filter_id]['range_values'] = $field_range_values[$filter_id];
                    }
                }

                return array($_f, $_view_all);
            }
        }

        foreach ($filters as $filter_id => $filter) {
            if (!empty($field_range_values[$filter_id]) || !empty($merged[$filter_id]) && empty($params['view_all']) || (!empty($params['filter_id']) && $params['filter_id'] != $filter_id)) {

                // Check if filter range was selected
                if (!empty($merged[$filter_id])) {
                    if (empty($filters[$filter_id]['feature_id'])) {
                        $intersect = array_intersect(array_keys($merged[$filter_id]), $field_ranges_ids);
                    } else {
                        $intersect = array_intersect(array_keys($merged[$filter_id]), $ranges_ids);
                    }
                }
                if (!empty($intersect)) {
                    foreach ($merged[$filter_id] as $k => $v) {
                        if (!in_array($v['range_id'], $intersect)) {
                            // Unset unselected ranges
                            unset($merged[$filter_id][$k]);
                        }
                    }
                }

                if (!empty($field_range_values[$filter_id])) {
                    $filters[$filter_id]['range_values'] = $field_range_values[$filter_id];
                }

                $filters[$filter_id]['ranges'] = & $merged[$filter_id];

                // Add feature type to the filter
                if (!empty($merged[$filter_id])) {
                    $_first = reset($merged[$filter_id]);
                    if (!empty($_first['feature_type'])) {
                        $filters[$filter_id]['feature_type'] = $_first['feature_type'];
                    }
                }

                if (!empty($params['simple_link']) && $filters[$filter_id]['feature_type'] == 'E') {
                    $filters[$filter_id]['simple_link'] = true;
                }

                if (empty($params['advanced_filter']) && empty($params['skip_other_variants']) && !empty($filters[$filter_id]['ranges'])) {

                    $selected = array();
                    $features_hash = !empty($params['features_hash']) ? $params['features_hash'] : '';
                    foreach ($filters[$filter_id]['ranges'] as $_k => $r) {
                        if (fn_check_selected_filter($r['range_id'], !empty($r['feature_type']) ? $r['feature_type'] : '', $params, $filters[$filter_id]['field_type'])) {
                            // selected variant
                            $selected[$_k] = $r;
                            $selected[$_k]['selected'] = true;
                            unset($filters[$filter_id]['ranges'][$_k]);
                            $features_hash = fn_delete_range_from_url($features_hash, $r, $filters[$filter_id]['field_type']);
                        }
                    }
                    if (!empty($selected)) {
                        $selected_range_ids = array_keys($selected);
                        // Get other variants
                        $_params = $params;
                        //$_params['filter_id'] = $filter_id;
                        $_params['features_hash'] = $features_hash;
                        $_params['skip_other_variants'] = true;
                        unset($_params['variant_id'], $_params['check_location']);

                        list($_f) = fn_get_filters_products_count($_params);
                        if (!empty($_f[$filter_id])) {
                            $_f = $_f[$filter_id];
                            if (!empty($_f['ranges'])) {
                                // delete current range
                                foreach ($_f['ranges'] as $_rid => $_rv) {
                                    if (in_array($_rid, $selected_range_ids)) {
                                        unset($_f['ranges'][$_rid]);
                                    }
                                }
                                $filters[$filter_id]['ranges'] = $_f['ranges'];
                                $filters[$filter_id]['more_cut'] = !empty($_f['more_cut']) ? $_f['more_cut'] : false;
                            }
                        }
                        $filters[$filter_id]['selected_ranges'] = $selected;
                    }

                    if (!empty($params['only_selected'])) {
                        //unset($filters[$filter_id]['ranges']);
                        foreach ($filters[$filter_id]['ranges'] as $k => $v) {
                            $filters[$filter_id]['ranges'][$k]['disabled'] = true;
                        }
                    } else {
                        $_params = $params;
                        $_params['filter_id'] = $filter_id;
                        $_params['features_hash'] = '';
                        $_params['get_custom'] = true;
                        $_params['skip_other_variants'] = true;
                        unset($_params['variant_id'], $_params['check_location']);

                        list($_f) = fn_get_filters_products_count($_params);
                        if (!empty($_f[$filter_id])) {
                            $_f = $_f[$filter_id];
                            if (!empty($_f['ranges'])) {
                                foreach ($_f['ranges'] as $_rid => $_rv) {
                                    if (!isset($filters[$filter_id]['ranges'][$_rid]) && !isset($filters[$filter_id]['selected_ranges'][$_rid])) {
                                        $filters[$filter_id]['ranges'][$_rid] = $_rv;
                                        $filters[$filter_id]['ranges'][$_rid]['disabled'] = true;
                                        if (fn_check_selected_filter($_rv['range_id'], !empty($_rv['feature_type']) ? $_rv['feature_type'] : '', $params, !empty($_f[$filter_id]) ? $_f[$filter_id]['field_type'] : $_f['field_type'])) {
                                            $filters[$filter_id]['ranges'][$_rid]['checked'] = true;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Calculate number of ranges and compare with displaying count
                    if (empty($params['get_all'])) {
                        if (!empty($filters[$filter_id]['ranges'])) {
                            $count = count($filters[$filter_id]['ranges']);
                        } else {
                            $count = 1;
                        }
                        if ($count > $filters[$filter_id]['display_more_count']) {
                            $filters[$filter_id]['more_ranges'] = array_slice($filters[$filter_id]['ranges'], 0, $filters[$filter_id]['display_more_count'], true);
                            $count = $filters[$filter_id]['display_more_count'];
                            $filters[$filter_id]['more_cut'] = true;
                        } else {
                            $filters[$filter_id]['more_ranges'] = $filters[$filter_id]['ranges'];
                        }
                        $filters[$filter_id]['ranges'] = array_slice($filters[$filter_id]['more_ranges'], 0, $filters[$filter_id]['display_count'], true);
                        $filters[$filter_id]['more_ranges'] = array_slice($filters[$filter_id]['more_ranges'], $filters[$filter_id]['display_count'], $count, true);
                    }

                } else {
                    if (!empty($params['variant_id']) && !empty($filters[$filter_id]['ranges'][$params['variant_id']])) {
                        $filters[$filter_id]['ranges'][$params['variant_id']]['selected'] = true; // mark selected variant
                    }
                }

                continue;
                // If its "view all" page, return all ranges
            } elseif (!empty($params['filter_id']) && $params['filter_id'] == $filter_id && !empty($merged[$filter_id])) {

                if (empty($params['return_view_all'])) {
                    $filters[$filter_id]['ranges'] = & $merged[$filter_id];

                    $selected = array();
                    $features_hash = !empty($params['features_hash']) ? $params['features_hash'] : '';
                    foreach ($filters[$filter_id]['ranges'] as $_k => $r) {
                        if (fn_check_selected_filter($r['range_id'], !empty($r['feature_type']) ? $r['feature_type'] : '', $params, $filters[$filter_id]['field_type'])) {
                            $selected[$_k] = $r;
                            $selected[$_k]['selected'] = true;
                            unset($filters[$filter_id]['ranges'][$_k]);
                            $features_hash = fn_delete_range_from_url($features_hash, $r, $filters[$filter_id]['field_type']);
                            continue;
                        }
                    }
                    if (!empty($selected)) {
                        $selected_range_ids = array_keys($selected);
                        // Get other variants
                        $_params = $params;
                        $_params['filter_id'] = $filter_id;
                        $_params['features_hash'] = $features_hash;
                        $_params['skip_other_variants'] = true;
                        $_params['return_view_all'] = true;

                        unset($_params['variant_id'], $_params['check_location']);

                        list($_f, $view_all) = fn_get_filters_products_count($_params);
                    }
                }
                if (empty($view_all)) {
                    foreach ($merged[$filter_id] as $range) {
                        if (!empty($range['index']) || strlen($range['index']) > 0) { // feature
                            $view_all[$range['index']][] = $range;
                        } else { // custom range
                            $view_all[$filters[$range['filter_id']]['filter']][] = $range;
                        }
                    }
                    ksort($view_all);
                }
            }
            // Unset filter if it's empty
            unset($filters[$filter_id]);
        }

        if (!empty($params['advanced_filter'])) {
            $_params = array(
                'feature_types' => array('C', 'T'),
                'plain' => true,
                'category_ids' => array(empty($params['category_id']) ? 0 : $params['category_id'])
            );
            list($features) = fn_get_product_features($_params);

            if (!empty($features)) {
                $filters = array_merge($filters, $features);
            }
        }

        /**
         * Change product filters data
         *
         * @param array $filters  Product filters data
         * @param array $view_all All ranges filters
         * @param array $params Products filter search params
         */
        fn_set_hook('get_filters_products_count_before_select', $filters, $view_all, $params);
        Registry::set($key, array($filters, $view_all));
    } else {
        list($filters, $view_all) = Registry::get($key);
    }

    return array($filters, $view_all);
}

/**
 * Checks selected filter or unselected
 *
 * @param int $element_id element from filter
 * @param string $feature_type feature type
 * @param array $request_params request array
 * @param string $field_type type of product field (A - amount, P - price, etc)
 * @return bool true if filter selected or false otherwise
 */
function fn_check_selected_filter($element_id, $feature_type = '', $request_params = array(), $field_type = '')
{
    /**
     * Change parameters for selecting filter data
     *
     * @param int    $element_id     Element identifier
     * @param string $feature_type   Feature type
     * @param array  $request_params Request parameters
     * @param string $field_type     Product field type
     */
    fn_set_hook('check_selected_filter_pre', $element_id, $feature_type, $request_params, $field_type);

    $prefix = empty($field_type) ? (in_array($feature_type, array('N', 'O', 'D')) ? 'R' : 'V') : $field_type;

    $result = false;

    if (!empty($request_params['features_hash']) || !empty($request_params['req_range_id'])) {
        if (!empty($request_params['req_range_id']) && $request_params['req_range_id'] == $element_id) {
            $result = true;
        } else {
            $_tmp = explode('.', $request_params['features_hash']);
            if (in_array($prefix . $element_id, $_tmp)) {
                $result = true;
            }
        }
    }

    /**
     * Change selected filter check result
     *
     * @param boolean $result         Flag determines if filter is selected
     * @param int     $element_id     Element identifier
     * @param string  $feature_type   Feature type
     * @param array   $request_params Request parameters
     * @param string  $field_type     Product field type
     */
    fn_set_hook('check_selected_filter_post', $result, $element_id, $feature_type, $request_params, $field_type);

    return $result;
}

/**
 * Removes range from url (example - delete "R2" from "R2.V2.V11" - result "R2.V11")
 *
 * @param string $url url from wich will delete
 * @param array $range deleted element
 * @param string $field_type type of product field (A - amount, P - price, etc)
 * @return string
 */

function fn_delete_range_from_url($url, $range, $field_type = '')
{
    /**
     * Changes params before deleting range from the url hash
     *
     * @param string $url        Url hash from wich range should be deleted
     * @param array  $range      Range data
     * @param string $field_type Range field type
     */
    fn_set_hook('delete_range_from_url_pre', $url, $range, $field_type);

    $prefix = empty($field_type) ? (in_array($range['feature_type'], array('N', 'O', 'D')) ? 'R' : 'V') : $field_type;

    $element = $prefix . $range['range_id'];
    $pattern = '/(' . $element . '[\.]?)|([\.]?' . $element . ')(?![\d]+)/';

    $result = preg_replace($pattern, '', $url);

    /** Modifies result after removing range from URL hash
     *
     * @param string $result     URL hash not containing range
     * @param string $url        URL hash for range to be removed from
     * @param array  $range      Range data
     * @param string $field_type Range field type
     */
    fn_set_hook('delete_range_from_url_post', $result, $url, $range, $field_type);

    return $result;
}

/**
 * Adds range to hash (example - add "V2" to "R23.V11.R5" - result "R23.V11.R5.V2")
 *
 * @param string $hash hash to which will be added
 * @param array $range added element
 * @param string $field_type element prefix ("R" or "V")
 * @return string new hash
 */
function fn_add_range_to_url_hash($hash, $range, $field_type = '')
{
    /**
     * Changes params before adding range to url hash
     *
     * @param string $hash       URL hash to be added range
     * @param array  $range      Range data
     * @param string $field_type Range field type
     */
    fn_set_hook('add_range_to_url_hash_pre', $hash, $range, $field_type);

    if (!is_array($range)) {
        $_range['range_id'] = $range;
    } else {
        $_range = $range;
    }

    if (empty($field_type) && !empty($_range['field_type'])) {
        $field_type = $_range['field_type'];
    }

    if ($field_type == 'P') {
        //remove previous price diapason
        $pattern = '/(P\d+-\d+-\w+\.?)|(\.?P\d+-\d+-\w+)/';
        $hash = preg_replace($pattern, '', $hash);
    }

    if ($field_type == 'A') {
        //remove previous amount diapason
        $pattern = '/(A\d+-\d+\.?)|(\.?A\d+-\d+)/';
        $hash = preg_replace($pattern, '', $hash);
    }

    $prefix = empty($field_type) ? (in_array($_range['feature_type'], array('N', 'O', 'D')) ? 'R' : 'V') : $field_type;
    $result = '';
    if (empty($hash)) {
        $result = $prefix . $_range['range_id'];
    } elseif (strrpos($hash, $prefix . $_range['range_id']) === false) {
        $result = $hash . '.' . $prefix . $_range['range_id'];
    } elseif (preg_match('/' . $prefix . $_range['range_id'] . '[0-9]+/', $hash)) {
        $result = $hash . '.' . $prefix . $_range['range_id'];
    } else {
        $result = $hash;
    }

    /**
     * Changes params before adding range to url hash
     *
     * @param string $result     URL hash containing range
     * @param string $hash       URL hash to be added range
     * @param array  $range      Range data
     * @param string $field_type Range field type
     */
    fn_set_hook('add_range_to_url_hash_post', $result, $hash, $range, $field_type);

    return $result;
}

/**
 * Adds selected filter ranges to the breadcrumbs
 *
 * @param array $request Request data
 * @param string $url Breadcrumb url
 * @return boolean Always true
 */
function fn_add_filter_ranges_breadcrumbs($request, $url = '')
{
    /**
     * Adds additional actions before adding filter ranges breadcrumbs
     *
     * @param array  $request Request data
     * @param string $url     Breadcrumb url
     */
    fn_set_hook('add_filter_ranges_breadcrumbs_pre', $request, $url);

    if (empty($request['features_hash'])) {
        return false;
    }

    $parsed_ranges = fn_parse_features_hash($request['features_hash'], false);

    if (!empty($parsed_ranges[1])) {
        $features_hash = '';
        $last_type = array_pop($parsed_ranges[1]);
        $last_range_id = array_pop($parsed_ranges[2]);

        if (!empty($parsed_ranges)) {
            foreach ($parsed_ranges[1] as $k => $v) {
                $range = fn_get_filter_range_name($v, $parsed_ranges[2][$k]);
                $features_hash = fn_add_range_to_url_hash($features_hash, array('range_id' => $parsed_ranges[2][$k]), $v);
                fn_add_breadcrumb(html_entity_decode($range, ENT_COMPAT, 'UTF-8'), "$url&features_hash=" . $features_hash . (!empty($request['subcats']) ? '&subcats=Y' : ''), true);
            }
        }
        $range = fn_get_filter_range_name($last_type, $last_range_id);
        fn_add_breadcrumb(html_entity_decode($range, ENT_COMPAT, 'UTF-8'));

    }

    /**
     * Adds additional actions after adding filter ranges breadcrumbs
     *
     * @param array  $request Request data
     * @param string $url     Breadcrumb url
     */
    fn_set_hook('add_filter_ranges_breadcrumbs_post', $request, $url);

    return true;
}

/**
 * Gets filter range names
 *
 * @param string $range_type Range field type
 * @param int $range_id Range identifier
 * @return string Range name
 */
function fn_get_filter_range_name($range_type, $range_id)
{
    /**
     * Changes params for getting filter range name
     *
     * @param string $range_type Range field type
     * @param int    $range_id   Range identifier
     */
    fn_set_hook('get_filter_range_name_pre', $range_type, $range_id);

    static $fields;

    if (!isset($fields)) {
        $fields = fn_get_product_filter_fields();
    }

    if ($range_type == 'F') {
        $range_name = $fields['F']['variant_descriptions'][$range_id == 1 ? 'Y' : 'N'];
    } elseif ($range_type == 'P' || $range_type == 'A') {
        $data = explode('-', $range_id);
        $from_val = !empty($data[0]) ? $data[0] : 0;
        $to_val = !empty($data[1]) ? $data[1] : 0;
        $add_val = !empty($data[2]) ? $data[2] : 0;
        if (empty($add_val) && $range_type == 'P') {
            $add_val = CART_SECONDARY_CURRENCY;
        }

        $field_name = '';
        $from = fn_strtolower(__('range_from'));
        $to = fn_strtolower(__('range_to'));
        if ($range_type == 'P') {
            $field_name = __('price');
            if ($add_val != CART_SECONDARY_CURRENCY) {
                $prev_coef = Registry::get('currencies.' . $add_val . '.coefficient');
                $cur_coef  = Registry::get('currencies.' . CART_SECONDARY_CURRENCY . '.coefficient');
                $from_val = floor(floatval($from_val) * floatval($prev_coef) / floatval($cur_coef));
                $to_val = ceil(floatval($to_val) * floatval($prev_coef) / floatval($cur_coef));
                $add_val = CART_SECONDARY_CURRENCY;
            }

            $add_val = Registry::get('currencies.' . $add_val . '.symbol');
        } elseif ($range_type == 'A') {
            $field_name = __('amount');
        }

        $range_name = "$field_name : $from $from_val $to $to_val " . (!empty($add_val) ? $add_val : '');
    } else {
        $range_name = ($range_type == 'V') ? db_get_field("SELECT variant FROM ?:product_feature_variant_descriptions WHERE variant_id = ?i AND lang_code = ?s", $range_id, CART_LANGUAGE) : db_get_field("SELECT range_name FROM ?:product_filter_ranges_descriptions WHERE range_id = ?i AND lang_code = ?s", $range_id, CART_LANGUAGE);
    }

    /**
     * Changes range name
     *
     * @param string $range_name Ramge name
     * @param string $range_type Range field type
     * @param int    $range_id   Range identifier
     */
    fn_set_hook('get_filter_range_name_post', $range_name, $range_type, $range_id);

    return fn_text_placeholders($range_name);
}

function fn_delete_product_filter($filter_id)
{
    /**
     * Adds additional actions before deleting product filter
     *
     * @param int $filter_id Filter identifier
     */
    fn_set_hook('delete_product_filter_pre', $filter_id);

    $range_ids = db_get_fields("SELECT range_id FROM ?:product_filter_ranges WHERE filter_id = ?i", $filter_id);

    fn_set_hook('delete_product_filter', $filter_id, $range_ids);

    db_query("DELETE FROM ?:product_filters WHERE filter_id = ?i", $filter_id);
    db_query("DELETE FROM ?:product_filter_descriptions WHERE filter_id = ?i", $filter_id);

    foreach ($range_ids as $range_id) {
        db_query("DELETE FROM ?:product_filter_ranges_descriptions WHERE range_id = ?i", $range_id);
    }

    db_query("DELETE FROM ?:product_filter_ranges WHERE filter_id = ?i", $filter_id);

    /**
     * Adds additional actions after deleting product filter
     *
     * @param int $filter_id Filter identifier
     */
    fn_set_hook('delete_product_filter_post', $filter_id);

    return true;
}

function fn_parse_features_hash($features_hash = '', $values = true)
{
    /**
     * Changes parameters before parsing features hash
     *
     * @param string $features_hash Features hash
     * @param bool   $values        Flag determines if feature values should be returned
     */
    fn_set_hook('parse_features_hash_pre', $features_hash, $values);

    $result = array();

    if (!empty($features_hash)) {
        $variants_ids = $ranges_ids = $fields_ids = $slider_vals = $fields_ids_revert = array();
        preg_match_all('/([A-Z]+)([\d]+-?\d*-?\w*)[,]?/', $features_hash, $vals);

        if ($values !== true) {
            return $vals;
        }

        $fields = fn_get_product_filter_fields();

        if (!empty($vals) && !empty($vals[1]) && !empty($vals[2])) {
            foreach ($vals[1] as $key => $range_type) {
                if ($range_type == 'V') {
                    // Feature variants
                    $variants_ids[] = $vals[2][$key];
                } elseif ($range_type == 'R') {
                    // Feature ranges
                    $ranges_ids[] = $vals[2][$key];
                } elseif (!empty($fields[$range_type]['slider'])) {
                    $slider_vals[$vals[1][$key]] = explode('-', $vals[2][$key]);
                } else {
                    // Product field ranges
                    $fields_ids[$vals[2][$key]] = $vals[1][$key];
                    $fields_ids_revert[$vals[1][$key]][] = $vals[2][$key];
                }
            }
        }

        $variants_ids = array_map('intval', $variants_ids);
        $ranges_ids = array_map('intval', $ranges_ids);

        $result = array($variants_ids, $ranges_ids, $fields_ids, $slider_vals, $fields_ids_revert);
    }

    /**
     * Changes parsed features hash data
     *
     * @param array  $result        Parsed feature hash data
     * @param string $features_hash Features hash
     * @param bool   $values        Flag determines if feature values should be returned
     */
    fn_set_hook('parse_features_hash_post', $result, $features_hash, $values);

    return $result;
}

/**
 * Generates fields for the product filters
 * Returns array with following structure:
 *
 * code => array (
 * 		'db_field' => db_field,
 * 		'table' => db_table,
 * 		'name' => lang_var_name,
 * 		'condition_type' => condition_type
 * );
 *
 * condition_type - contains "C" - char (example, free_shipping == "Y")
 * 							 "D" - dinamic (1.23 < price < 3.45)
 * 							 "F" - fixed (supplier_id = 3)
 *
 * slider - boolean, if true then the slider will be displaed for choosing the range of values
 * is_range - boolean, show or not ranges
 *
 */
function fn_get_product_filter_fields()
{
    $filters = array (
        // price filter
        'P' => array (
            'db_field' => 'price',
            'table' => 'product_prices',
            'description' => 'price',
            'condition_type' => 'D',
            'slider' => true,
        ),
        // amount filter
        'A' => array (
            'db_field' => 'amount',
            'table' => 'products',
            'description' => 'in_stock',
            'condition_type' => 'D',
            'slider' => true,
            'hidden' => true,
        ),
        // filter by free shipping
        'F' => array (
            'db_field' => 'free_shipping',
            'table' => 'products',
            'description' => 'free_shipping',
            'condition_type' => 'C',
            'variant_descriptions' => array (
                'Y' => __('yes'),
                'N' => __('no')
            )
        )
    );

    /**
     * Changes product filter fields data
     *
     * @param array $filters Product filter fields
     */
    fn_set_hook('get_product_filter_fields', $filters);

    return $filters;
}

//
//Gets all combinations of options stored in exceptions
//
function fn_get_product_exceptions($product_id, $short_list = false)
{
    if (fn_allowed_for('ULTIMATE:FREE')) {
        return array();
    }

    /**
     * Changes params before getting product exceptions
     *
     * @param int     $product_id Product identifier
     * @param boolean $short_list Flag determines if exceptions list should be returned in short format
     */
    fn_set_hook('get_product_exceptions_pre', $product_id, $short_list);

    $exceptions = db_get_array("SELECT * FROM ?:product_options_exceptions WHERE product_id = ?i ORDER BY exception_id", $product_id);

    foreach ($exceptions as $k => $v) {
        $exceptions[$k]['combination'] = unserialize($v['combination']);

        if ($short_list) {
            $exceptions[$k] = $exceptions[$k]['combination'];
        }
    }

    /**
     * Changes product exceptions data
     *
     * @param int     $product_id Product identifier
     * @param array   $exceptions Exceptions data
     * @param boolean $short_list Flag determines if exceptions list should be returned in short format
     */
    fn_set_hook('get_product_exceptions_post', $product_id, $exceptions, $short_list);

    return $exceptions;
}


//
// Returnns true if such combination already exists
//
function fn_check_combination($combinations, $product_id)
{
    /**
     * Changes params before checking combination
     *
     * @param array $combinations Combinations data
     * @param int   $product_id   Product identifier
     */
    fn_set_hook('check_combination_pre', $combinations, $product_id);

    $exceptions = fn_get_product_exceptions($product_id);

    $result = false;

    if (!empty($exceptions)) {
        foreach ($exceptions as $k => $v) {
            $temp = array();
            $temp = $v['combination'];
            foreach ($combinations as $key => $value) {
                if ((in_array($value, $temp)) && ($temp[$key] == $value)) {
                    unset($temp[$key]);
                }
            }
            if (empty($temp)) {
                $result = true;
                break;
            }
        }
    }

    /**
     * Changes params before checking combination
     *
     * @param boolean $result       Flag determines if combination exists
     * @param array   $combinations Combinations data
     * @param int     $product_id   Product identifier
     */
    fn_set_hook('check_combination_post', $result, $combinations, $product_id);

    return $result;
}

//
// Updates options exceptions using product_id;
//
function fn_update_exceptions($product_id)
{
    $result = false;

    if ($product_id) {

        $exceptions = fn_get_product_exceptions($product_id);

        /**
         * Adds additional actions before product exceptions update
         *
         * @param int $product_id Product identifier
         * @param array $exceptions
         */
        fn_set_hook('update_exceptions_pre', $product_id, $exceptions);

        if (!empty($exceptions)) {
            db_query("DELETE FROM ?:product_options_exceptions WHERE product_id = ?i", $product_id);
            foreach ($exceptions as $k => $v) {
                $_options_order = db_get_fields("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_global_option_links as b ON a.option_id = b.option_id WHERE a.product_id = ?i OR b.product_id = ?i ORDER BY position", $product_id, $product_id);

                if (empty($_options_order)) {
                    return false;
                }
                $combination  = array();

                foreach ($_options_order as $option) {
                    if (!empty($v['combination'][$option])) {
                        $combination[$option] = $v['combination'][$option];
                    } else {
                        $combination[$option] = -1;
                    }
                }

                $_data = array(
                    'product_id' => $product_id,
                    'exception_id' => $v['exception_id'],
                    'combination' => serialize($combination),
                );
                db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);

            }

            $result = true;
        }

        /**
         * Adds additional actions after product exceptions update
         *
         * @param int $product_id Product identifier
         * @param array $exceptions
         */
        fn_set_hook('update_exceptions_post', $product_id, $exceptions);
    }

    return $result;
}

//
// Clone exceptions
//
function fn_clone_options_exceptions(&$exceptions, $old_opt_id, $old_var_id, $new_opt_id, $new_var_id)
{
    /**
     * Adds additional actions before options exceptions clone
     *
     * @param array $exceptions Exceptions array
     * @param int   $old_opt_id Old option identifier
     * @param int   $old_var_id Old variant identifier
     * @param int   $new_opt_id New option identifier
     * @param int   $new_var_id New variant identifier
     */
    fn_set_hook('clone_options_exceptions_pre', $exceptions, $old_opt_id, $old_var_id, $new_opt_id, $new_var_id);

    foreach ($exceptions as $key => $value) {
        foreach ($value['combination'] as $option => $variant) {
            if ($option == $old_opt_id) {
                $exceptions[$key]['combination'][$new_opt_id] = $variant;
                unset($exceptions[$key]['combination'][$option]);

                if ($variant == $old_var_id) {
                    $exceptions[$key]['combination'][$new_opt_id] = $new_var_id;
                }
            }
            if ($variant == $old_var_id) {
                $exceptions[$key]['combination'][$option] = $new_var_id;
            }
        }
    }

    /**
     * Adds additional actions after options exceptions clone
     *
     * @param array $exceptions Exceptions array
     * @param int   $old_opt_id Old option identifier
     * @param int   $old_var_id Old variant identifier
     * @param int   $new_opt_id New option identifier
     * @param int   $new_var_id New variant identifier
     */
    fn_set_hook('clone_options_exceptions_post', $exceptions, $old_opt_id, $old_var_id, $new_opt_id, $new_var_id);
}

/**
 * This function clones options to product from a product or from a global option
 *
 * @param int $from_product_id Identifier of product from that options are copied
 * @param int $to_product_id Identifier of product to that options are copied
 * @param type $from_global_option_id Identifier of the global option or false (if options are copied from product)
 */
function fn_clone_product_options($from_product_id, $to_product_id, $from_global_option_id = false)
{
    /**
     * Adds additional actions before poduct options clone
     *
     * @param int         $from_product_id       Identifier of product from that options are copied
     * @param int         $to_product_id         Identifier of product to that options are copied
     * @param int/boolean $from_global_option_id Identifier of the global option or false (if options are copied from product)
     */
    fn_set_hook('clone_product_options_pre', $from_product_id, $to_product_id, $from_global_option_id);

    // Get all product options assigned to the product
    $id_req = (empty($from_global_option_id)) ? db_quote('product_id = ?i', $from_product_id) : db_quote('option_id = ?i', $from_global_option_id);
    $data = db_get_array("SELECT * FROM ?:product_options WHERE $id_req");
    $linked  = db_get_field("SELECT COUNT(option_id) FROM ?:product_global_option_links WHERE product_id = ?i", $from_product_id);
    if (!empty($data) || !empty($linked)) {
        // Get all exceptions for the product
        if (!empty($from_product_id)) {
            if (!fn_allowed_for('ULTIMATE:FREE')) {
                $exceptions = fn_get_product_exceptions($from_product_id);
            }

            $inventory = db_get_field("SELECT COUNT(*) FROM ?:product_options_inventory WHERE product_id = ?i", $from_product_id);
        }
        // Fill array of options for linked global options options
        $change_options = array();
        $change_varaiants = array();
        // If global option are linked than ids will be the same
        $change_options = db_get_hash_single_array("SELECT option_id FROM ?:product_global_option_links WHERE product_id = ?i", array('option_id', 'option_id'), $from_product_id);
        if (!empty($change_options)) {
            foreach ($change_options as $value) {
                $change_varaiants = fn_array_merge(db_get_hash_single_array("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i", array('variant_id', 'variant_id'), $value), $change_varaiants, true);
            }
        }
        foreach ($data as $v) {
            // Clone main data
            $option_id = $v['option_id'];
            $v['product_id'] = $to_product_id;
            if (fn_allowed_for('ULTIMATE') || fn_allowed_for('MULTIVENDOR')) {
                $product_company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $to_product_id);
                $v['company_id'] = Registry::ifGet('runtime.company_id', $product_company_id);
            } else {
                $v['company_id'] = Registry::get('runtime.company_id');
            }
            unset($v['option_id']);
            $new_option_id = db_query("INSERT INTO ?:product_options ?e", $v);

            if (fn_allowed_for('ULTIMATE')) {
                fn_ult_share_product_option($new_option_id, $to_product_id);
            }

            // Clone descriptions
            $_data = db_get_array("SELECT * FROM ?:product_options_descriptions WHERE option_id = ?i", $option_id);
            foreach ($_data as $_v) {
                $_v['option_id'] = $new_option_id;
                db_query("INSERT INTO ?:product_options_descriptions ?e", $_v);
            }

            $change_options[$option_id] = $new_option_id;
            // Clone variants if exists
            if ($v['option_type'] == 'S' || $v['option_type'] == 'R' || $v['option_type'] == 'C') {
                $_data = db_get_array("SELECT * FROM ?:product_option_variants WHERE option_id = ?i", $option_id);
                foreach ($_data as $_v) {
                    $variant_id = $_v['variant_id'];
                    $_v['option_id'] = $new_option_id;
                    unset($_v['variant_id']);
                    $new_variant_id = db_query("INSERT INTO ?:product_option_variants ?e", $_v);
                    if (!fn_allowed_for('ULTIMATE:FREE')) {
                        // Clone Exceptions
                        if (!empty($exceptions)) {
                            fn_clone_options_exceptions($exceptions, $option_id, $variant_id, $new_option_id, $new_variant_id);
                        }
                    }
                    $change_varaiants[$variant_id] = $new_variant_id;
                    // Clone descriptions
                    $__data = db_get_array("SELECT * FROM ?:product_option_variants_descriptions WHERE variant_id = ?i", $variant_id);
                    foreach ($__data as $__v) {
                        $__v['variant_id'] = $new_variant_id;
                        db_query("INSERT INTO ?:product_option_variants_descriptions ?e", $__v);
                    }

                    // Clone variant images
                    fn_clone_image_pairs($new_variant_id, $variant_id, 'variant_image');
                }
                unset($_data, $__data);
            }

            /**
             * Adds additional actions after cloning each product option
             *
             * @param int $from_product_id Identifier of product from that options are copied
             * @param int $to_product_id Identifier of product to that options are copied
             * @param int/boolean $from_global_option_id Identifier of the global option or false (if options are copied from product)
             * @param array $v Product option data
             */
            fn_set_hook('clone_product_option_post', $from_product_id, $to_product_id, $from_global_option_id, $v);
        }
        // Clone Inventory
        if (!empty($inventory)) {
            fn_clone_options_inventory($from_product_id, $to_product_id, $change_options, $change_varaiants);
        }

        if (!fn_allowed_for('ULTIMATE:FREE')) {
            if (!empty($exceptions)) {
                foreach ($exceptions as $k => $v) {
                    $_data = array(
                        'product_id' => $to_product_id,
                        'combination' => serialize($v['combination']),
                    );
                    db_query("INSERT INTO ?:product_options_exceptions ?e", $_data);
                }
            }
        }
    }

    /**
     * Adds additional actions after poduct options clone
     *
     * @param int         $from_product_id       Identifier of product from that options are copied
     * @param int         $to_product_id         Identifier of product to that options are copied
     * @param int/boolean $from_global_option_id Identifier of the global option or false (if options are copied from product)
     */
    fn_set_hook('clone_product_options_post', $from_product_id, $to_product_id, $from_global_option_id);
}

//
// Clone Inventory
//
function fn_clone_options_inventory($from_product_id, $to_product_id, $options, $variants)
{
    /**
     * Adds additional actions before options inventory clone
     *
     * @param int   $from_product_id Identifier of product from that options are copied
     * @param int   $to_product_id   Identifier of product to that options are copied
     * @param array $options         Array with options identifiers where old identifiers points to new identifier
     * @param array $variants        Array with variant identifiers where old identifiers points to new identifier
     */
    fn_set_hook('clone_options_inventory_pre', $from_product_id, $to_product_id, $options, $variants);

    $inventory = db_get_array("SELECT * FROM ?:product_options_inventory WHERE product_id = ?i", $from_product_id);

    foreach ($inventory as $key => $value) {
        $_variants = explode('_', $value['combination']);
        $inventory[$key]['combination'] = '';
        foreach ($_variants as $kk => $vv) {
            if (($kk % 2) == 0 && !empty($_variants[$kk + 1])) {
                $_comb[0] = $options[$vv];
                $_comb[1] = $variants[$_variants[$kk + 1]];

                $new_variants[$kk] = $_comb[1];
                $inventory[$key]['combination'] .= implode('_', $_comb) . (!empty($_variants[$kk + 2]) ? '_' : '');
            }
        }

        $_data['product_id'] = $to_product_id;
        $_data['combination_hash'] = fn_generate_cart_id($to_product_id, array('product_options' => $new_variants));
        $_data['combination'] = rtrim($inventory[$key]['combination'], "|");
        $_data['amount'] = $value['amount'];
        $_data['product_code'] = $value['product_code'];
        $_data['position'] = $value['position'];
        db_query("INSERT INTO ?:product_options_inventory ?e", $_data);

        // Clone option images
        fn_clone_image_pairs($_data['combination_hash'], $value['combination_hash'], 'product_option');
    }

    /**
     * Adds additional actions after options inventory clone
     *
     * @param int   $from_product_id Identifier of product from that options are copied
     * @param int   $to_product_id   Identifier of product to that options are copied
     * @param array $options         Array with options identifiers where old identifier points to new identifier
     * @param array $variants        Array with variant identifiers where old identifier points to new identifier
     */
    fn_set_hook('clone_options_inventory_post', $from_product_id, $to_product_id, $options, $variants);
}

/**
 * Generate url-safe name for the object
 * Example:
 *  Hello, World! => hello-world
 *  Русский код => russky-kod
 *
 * @param string $str String to be checked and converted
 * @param string $object_type Extra string, object type (e.g.: 'products', 'categories'). Result: some-string-products
 * @param int $object_id Extra string, Object identifier. Result: some-string-products-34
 * @param boolean $is_multi_lang Support multi-language names
 * @return string Url-safe name
 */
function fn_generate_name($str, $object_type = '', $object_id = 0, $is_multi_lang = false)
{
    /**
     * Change parameters for generating file name
     *
     * @param string $str         Basic file name
     * @param string $object_type Object type
     * @param int    $object_id   Object identifier
     */
    fn_set_hook('generate_name_pre', $str, $object_type, $object_id);

    $delimiter = SEO_DELIMITER;
    $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8'); // convert html special chars back to original chars

    $result = '';

    if (!empty($str)) {
        if ($is_multi_lang) {
            $literals = "/[^a-z\p{Ll}\p{Lu}\p{Lt}\p{Lo}\p{Nd}\p{Pc}\p{Mn}0-9-\.]/u";
            $convert_letters = fn_get_schema('literal_converter', 'general');
        } else {
            $literals = "/[^a-z0-9-\.]/";
            $convert_letters = fn_get_schema('literal_converter', 'schema');
        }
        $str = strtr($str, $convert_letters);

        if (!empty($object_type)) {
            $str .= $delimiter . $object_type . $object_id;
        }

        $str = fn_strtolower($str); // only lower letters
        $str = preg_replace($literals, '', $str); // URL can contain latin letters, numbers, dashes and points only
        $str = preg_replace("/($delimiter){2,}/", $delimiter, $str); // replace double (and more) dashes with one dash

        $result = trim($str, '-'); // remove trailing dash if exist
    }

    /**
     * Change generated file name
     *
     * @param string $result      Generated file name
     * @param string $str         Basic file name
     * @param string $object_type Object type
     * @param int    $object_id   Object identifier
     */
    fn_set_hook('generate_name_post', $result, $str, $object_type, $object_id);

    return $result;
}

/**
 * FConstructs a string in format option1_variant1_option2_variant2...
 *
 * @param array $product_options
 * @return string
 */
function fn_get_options_combination($product_options)
{
    /**
     * Changes params for generating options combination
     *
     * @param array $product_options Array with selected options values
     */
    fn_set_hook('get_options_combination_pre', $product_options);

    if (empty($product_options) && !is_array($product_options)) {
        return '';
    }

    $combination = '';
    foreach ($product_options as $option => $variant) {
        $combination .= $option . '_' . $variant . '_';
    }
    $combination = trim($combination, '_');

    /**
     * Changes options combination
     *
     * @param array  $product_options Array with selected options values
     * @param string $combination     Generated combination
     */
    fn_set_hook('get_options_combination_post', $product_options, $combination);

    return $combination;
}

function fn_get_products($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    /**
     * Changes params for selecting products
     *
     * @param array  $params         Product search params
     * @param int    $items_per_page Items per page
     * @param string $lang_code      Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_products_pre', $params, $items_per_page, $lang_code);

    // Init filter
    $params = LastView::instance()->update('products', $params);

    // Set default values to input params
    $default_params = array (
        'area' => AREA,
        'extend' => array('product_name', 'prices', 'categories'),
        'custom_extend' => array(),
        'pname' => '',
        'pshort' => '',
        'pfull' => '',
        'pkeywords' => '',
        'feature' => array(),
        'type' => 'simple',
        'page' => 1,
        'action' => '',
        'variants' => array(),
        'ranges' => array(),
        'custom_range' => array(),
        'field_range' => array(),
        'features_hash' => '',
        'limit' => 0,
        'bid' => 0,
        'match' => '',
        'tracking' => array(),
        'get_frontend_urls' => false,
        'items_per_page' => $items_per_page
    );
    if (empty($params['custom_extend'])) {
        $params['extend'] = !empty($params['extend']) ? array_merge($default_params['extend'], $params['extend']) : $default_params['extend'];
    } else {
        $params['extend'] = $params['custom_extend'];
    }

    $params = array_merge($default_params, $params);

    if ((empty($params['pname']) || $params['pname'] != 'Y') && (empty($params['pshort']) || $params['pshort'] != 'Y') && (empty($params['pfull']) || $params['pfull'] != 'Y') && (empty($params['pkeywords']) || $params['pkeywords'] != 'Y') && (empty($params['feature']) || $params['feature'] != 'Y') && !empty($params['q'])) {
        $params['pname'] = 'Y';
    }

    $auth = & $_SESSION['auth'];

    // Define fields that should be retrieved
    if (empty($params['only_short_fields'])) {
        $fields = array (
            'products.*',
        );
    } else {
        $fields = array (
            'product_id' => 'products.product_id',
            'product_code' => 'products.product_code',
            'product_type' => 'products.product_type',
            'status' => 'products.status',
            'company_id' => 'products.company_id',
            'list_price' => 'products.list_price',
            'amount' => 'products.amount',
            'weight' => 'products.weight',
            'tracking' => 'products.tracking',
            'is_edp' => 'products.is_edp',
        );
    }

    // Define sort fields
    $sortings = array (
        'code' => 'products.product_code',
        'status' => 'products.status',
        'product' => 'product',
        'position' => 'products_categories.position',
        'price' => 'price',
        'list_price' => 'products.list_price',
        'weight' => 'products.weight',
        'amount' => 'products.amount',
        'timestamp' => 'products.timestamp',
        'updated_timestamp' => 'products.updated_timestamp',
        'popularity' => 'popularity.total',
        'company' => 'company_name',
        'null' => 'NULL'
    );

    if (!empty($params['get_subscribers'])) {
        $sortings['num_subscr'] = 'num_subscr';
        $fields['num_subscr'] = 'COUNT(DISTINCT product_subscriptions.subscription_id) as num_subscr';
    }

    if (!empty($params['order_ids'])) {
        $sortings['p_qty'] = 'purchased_qty';
        $sortings['p_subtotal'] = 'purchased_subtotal';
        $fields['purchased_qty'] = "SUM(?:order_details.amount) as purchased_qty";
        $fields['purchased_subtotal'] = "SUM(?:order_details.price * ?:order_details.amount) as purchased_subtotal";
    }

    if (isset($params['compact']) && $params['compact'] == 'Y') {
        $union_condition = ' OR ';
    } else {
        $union_condition = ' AND ';
    }

    $join = $condition = $u_condition = $inventory_condition = '';
    $having = array();

    // Search string condition for SQL query
    if (isset($params['q']) && fn_string_not_empty($params['q'])) {

        $params['q'] = trim($params['q']);
        if ($params['match'] == 'any') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' OR ';
        } elseif ($params['match'] == 'all') {
            $pieces = fn_explode(' ', $params['q']);
            $search_type = ' AND ';
        } else {
            $pieces = array($params['q']);
            $search_type = '';
        }

        $_condition = array();
        foreach ($pieces as $piece) {
            if (strlen($piece) == 0) {
                continue;
            }

            $tmp = db_quote("(descr1.search_words LIKE ?l)", '%' . $piece . '%'); // check search words

            if ($params['pname'] == 'Y') {
                $tmp .= db_quote(" OR descr1.product LIKE ?l", '%' . $piece . '%');
            }
            if ($params['pshort'] == 'Y') {
                $tmp .= db_quote(" OR descr1.short_description LIKE ?l", '%' . $piece . '%');
                $tmp .= db_quote(" OR descr1.short_description LIKE ?l", '%' . htmlentities($piece, ENT_QUOTES, 'UTF-8') . '%');
            }
            if ($params['pfull'] == 'Y') {
                $tmp .= db_quote(" OR descr1.full_description LIKE ?l", '%' . $piece . '%');
                $tmp .= db_quote(" OR descr1.full_description LIKE ?l", '%' . htmlentities($piece, ENT_QUOTES, 'UTF-8') . '%');
            }
            if ($params['pkeywords'] == 'Y') {
                $tmp .= db_quote(" OR (descr1.meta_keywords LIKE ?l OR descr1.meta_description LIKE ?l)", '%' . $piece . '%', '%' . $piece . '%');
            }
            if (!empty($params['feature']) && $params['action'] != 'feature_search') {
                $tmp .= db_quote(" OR ?:product_features_values.value LIKE ?l", '%' . $piece . '%');
            }

            fn_set_hook('additional_fields_in_search', $params, $fields, $sortings, $condition, $join, $sorting, $group_by, $tmp, $piece, $having);

            $_condition[] = '(' . $tmp . ')';
        }

        $_cond = implode($search_type, $_condition);

        if (!empty($_condition)) {
            $condition .= ' AND (' . $_cond . ') ';
        }

        if (!empty($params['feature']) && $params['action'] != 'feature_search') {
            $join .= " LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = products.product_id";
            $condition .= db_quote(" AND (?:product_features_values.feature_id IN (?n) OR ?:product_features_values.feature_id IS NULL)", array_values($params['feature']));
        }

        //if perform search we also get additional fields
        if ($params['pname'] == 'Y') {
            $params['extend'][] = 'product_name';
        }

        if ($params['pshort'] == 'Y' || $params['pfull'] == 'Y' || $params['pkeywords'] == 'Y') {
            $params['extend'][] = 'description';
        }

        unset($_condition);
    }

    //
    // [Advanced and feature filters]
    //

    if (!empty($params['apply_limit']) && $params['apply_limit'] && !empty($params['pid'])) {
        $pids = array();

        foreach ($params['pid'] as $pid) {
            if ($pid != $params['exclude_pid']) {
                if (count($pids) == $params['limit']) {
                    break;
                } else {
                    $pids[] = $pid;
                }
            }
        }
        $params['pid'] = $pids;
    }

    if (!empty($params['features_hash']) || (!fn_is_empty($params['variants'])) || !empty($params['feature_code'])) {
        $join .= db_quote(" LEFT JOIN ?:product_features_values ON ?:product_features_values.product_id = products.product_id AND ?:product_features_values.lang_code = ?s", $lang_code);
    }

    if (!empty($params['variants'])) {
        $params['features_hash'] .= implode('.', $params['variants']);
    }

    // Feature code
    if (!empty($params['feature_code'])) {
        $join .= db_quote(" LEFT JOIN ?:product_features ON ?:product_features_values.feature_id = ?:product_features.feature_id");
        $condition .= db_quote(" AND ?:product_features.feature_code = ?s", $params['feature_code']);
    }

    $advanced_variant_ids = $simple_variant_ids = $ranges_ids = $fields_ids = $fields_ids_revert = $slider_vals = array();

    if (!empty($params['features_hash'])) {
        list($av_ids, $ranges_ids, $fields_ids, $slider_vals, $fields_ids_revert) = fn_parse_features_hash($params['features_hash']);
        $advanced_variant_ids = db_get_hash_multi_array("SELECT feature_id, variant_id FROM ?:product_feature_variants WHERE variant_id IN (?n)", array('feature_id', 'variant_id'), $av_ids);
    }

    if (!empty($params['multiple_variants'])) {
        $simple_variant_ids = $params['multiple_variants'];
    }

    if (!empty($advanced_variant_ids)) {
        $join .= db_quote(" LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS advanced_variants FROM ?:product_features_values WHERE lang_code = ?s GROUP BY product_id) AS pfv_advanced ON pfv_advanced.product_id = products.product_id", $lang_code);

        $where_and_conditions = array();
        foreach ($advanced_variant_ids as $k => $variant_ids) {
            $where_or_conditions = array();
            foreach ($variant_ids as $variant_id => $v) {
                $where_or_conditions[] = db_quote(" FIND_IN_SET('?i', advanced_variants)", $variant_id);
            }
            $where_and_conditions[] = '(' . implode(' OR ', $where_or_conditions) . ')';
        }
        $condition .= ' AND ' . implode(' AND ', $where_and_conditions);
    }

    if (!empty($simple_variant_ids)) {
        $join .= db_quote(" LEFT JOIN (SELECT product_id, GROUP_CONCAT(?:product_features_values.variant_id) AS simple_variants FROM ?:product_features_values WHERE lang_code = ?s GROUP BY product_id) AS pfv_simple ON pfv_simple.product_id = products.product_id", $lang_code);

        $where_conditions = array();
        foreach ($simple_variant_ids as $k => $variant_id) {
            $where_conditions[] = db_quote(" FIND_IN_SET('?i', simple_variants)", $variant_id);
        }
        $condition .= ' AND ' . implode(' AND ', $where_conditions);
    }

    //
    // Ranges from text inputs
    //

    // Feature ranges
    if (!empty($params['custom_range'])) {
        foreach ($params['custom_range'] as $k => $v) {
            $k = intval($k);
            if (isset($v['from']) && fn_string_not_empty($v['from']) || isset($v['to']) && fn_string_not_empty($v['to'])) {
                if (!empty($v['type'])) {
                    if ($v['type'] == 'D') {
                        $v['from'] = fn_parse_date($v['from']);
                        $v['to'] = fn_parse_date($v['to']);
                    }
                }
                $join .= db_quote(" LEFT JOIN ?:product_features_values as custom_range_$k ON custom_range_$k.product_id = products.product_id AND custom_range_$k.lang_code = ?s", $lang_code);
                if (fn_string_not_empty($v['from']) && fn_string_not_empty($v['to'])) {
                    $condition .= db_quote(" AND (custom_range_$k.value_int >= ?i AND custom_range_$k.value_int <= ?i AND custom_range_$k.value = '' AND custom_range_$k.feature_id = ?i) ", $v['from'], $v['to'], $k);
                } else {
                    $condition .= " AND custom_range_$k.value_int" . (fn_string_not_empty($v['from']) ? db_quote(' >= ?i', $v['from']) : db_quote(" <= ?i AND custom_range_$k.value = '' AND custom_range_$k.feature_id = ?i ", $v['to'], $k));
                }
            }
        }
    }
    // Product field ranges
    $filter_fields = fn_get_product_filter_fields();
    if (!empty($params['field_range'])) {
        foreach ($params['field_range'] as $field_type => $v) {
            $structure = $filter_fields[$field_type];
            if (!empty($structure) && (!empty($v['from']) || !empty($v['to']))) {
                if ($field_type == 'P') { // price
                    $v['cur'] = !empty($v['cur']) ? $v['cur'] : CART_SECONDARY_CURRENCY;
                    if (empty($v['orig_cur'])) {
                        // saving the first user-entered values
                        // will be always search by it
                        $v['orig_from'] = $v['from'];
                        $v['orig_to'] = $v['to'];
                        $v['orig_cur'] = $v['cur'];
                        $params['field_range'][$field_type] = $v;
                    }
                    if ($v['orig_cur'] != CART_PRIMARY_CURRENCY) {
                        // calc price in primary currency
                        $cur_prim_coef  = Registry::get('currencies.' . $v['orig_cur'] . '.coefficient');
                        $decimals = Registry::get('currencies.' . CART_PRIMARY_CURRENCY . '.decimals');
                        $search_from = round($v['orig_from'] * floatval($cur_prim_coef), $decimals);
                        $search_to = round($v['orig_to'] * floatval($cur_prim_coef), $decimals);
                    } else {
                        $search_from = $v['orig_from'];
                        $search_to = $v['orig_to'];
                    }
                    // if user switch the currency, calc new values for displaying in filter
                    if ($v['cur'] != CART_SECONDARY_CURRENCY) {
                        if (CART_SECONDARY_CURRENCY == $v['orig_cur']) {
                            $v['from'] = $v['orig_from'];
                            $v['to'] = $v['orig_to'];
                        } else {
                            $prev_coef = Registry::get('currencies.' . $v['cur'] . '.coefficient');
                            $cur_coef  = Registry::get('currencies.' . CART_SECONDARY_CURRENCY . '.coefficient');
                            $v['from'] = floor(floatval($v['from']) * floatval($prev_coef) / floatval($cur_coef));
                            $v['to'] = ceil(floatval($v['to']) * floatval($prev_coef) / floatval($cur_coef));
                        }
                        $v['cur'] = CART_SECONDARY_CURRENCY;
                        $params['field_range'][$field_type] = $v;
                    }
                }

                $params["$structure[db_field]_from"] = trim(isset($search_from) ? $search_from : $v['from']);
                $params["$structure[db_field]_to"] = trim(isset($search_to) ? $search_to : $v['to']);
            }
        }
    }
    // Ranges from database
    if (!empty($ranges_ids)) {
        $filter_conditions = db_get_hash_multi_array("SELECT `from`, `to`, feature_id, filter_id, range_id FROM ?:product_filter_ranges WHERE range_id IN (?n)", array('filter_id', 'range_id'), $ranges_ids);
        $where_conditions = array();
        foreach ($filter_conditions as $fid => $range_conditions) {
            foreach ($range_conditions as $k => $range_condition) {
                $k = $fid . "_" . $k;
                $join .= db_quote(" LEFT JOIN ?:product_features_values as var_val_$k ON var_val_$k.product_id = products.product_id AND var_val_$k.lang_code = ?s", $lang_code);
                $where_conditions[] = db_quote("(var_val_$k.value_int >= ?i AND var_val_$k.value_int <= ?i AND var_val_$k.value = '' AND var_val_$k.feature_id = ?i)", $range_condition['from'], $range_condition['to'], $range_condition['feature_id']);
            }
            $condition .= db_quote(" AND (?p)", implode(" OR ", $where_conditions));
            $where_conditions = array();
        }
    }

    // Field ranges
    //$fields_ids = empty($params['fields_ids']) ? $fields_ids : $params['fields_ids'];
    if (!empty($params['fields_ids'])) {

        foreach ($fields_ids as $rid => $field_type) {
            if (!empty($filter_fields[$field_type])) {
                $structure = $filter_fields[$field_type];
                if ($structure['condition_type'] == 'D' && empty($structure['slider'])) {
                    $range_condition = db_get_row("SELECT `from`, `to`, range_id FROM ?:product_filter_ranges WHERE range_id = ?i", $rid);
                    if (!empty($range_condition)) {
                        $params["$structure[db_field]_from"] = $range_condition['from'];
                        $params["$structure[db_field]_to"] = $range_condition['to'];
                    }
                } elseif ($structure['condition_type'] == 'F') {
                    $params['filter_params'][$structure['db_field']][] = $rid;
                } elseif ($structure['condition_type'] == 'C') {
                    $params['filter_params'][$structure['db_field']][] = ($rid == 1) ? 'Y' : 'N';
                }
            }
        }
    } elseif (!empty($fields_ids_revert)) {
        foreach ($fields_ids_revert as $field_type => $rids) {
            if (!empty($filter_fields[$field_type])) {
                $structure = $filter_fields[$field_type];
                if ($structure['condition_type'] == 'D' && empty($structure['slider'])) {
                    foreach ($rids as $rid) {
                        $range_condition = db_get_row("SELECT `from`, `to`, range_id FROM ?:product_filter_ranges WHERE range_id = ?i", $rid);
                        if (!empty($range_condition)) {
                            $params["$structure[db_field]_from"] = $range_condition['from'];
                            $params["$structure[db_field]_to"] = $range_condition['to'];
                        }
                    }
                } elseif ($structure['condition_type'] == 'F') {
                    $params['filter_params'][$structure['db_field']] = $rids;
                } elseif ($structure['condition_type'] == 'C') {
                    if (count($rids) > 1) {
                        foreach ($rids as $rid) {
                            if ($fields_ids[$rid] == $field_type) {
                                unset($fields_ids[$rid]);
                            }
                            $params['features_hash'] = fn_delete_range_from_url($params['features_hash'], array('range_id' => $rid), $field_type);
                        }
                    } else {
                        $params['filter_params'][$structure['db_field']][] = ($rids[0] == 1) ? 'Y' : 'N';
                    }
                }
            }
        }
    }

    // Slider ranges
    $slider_vals = empty($params['slider_vals']) ? $slider_vals : $params['slider_vals'];
    if (!empty($slider_vals)) {
        foreach ($slider_vals as $field_type => $vals) {
            if (!empty($filter_fields[$field_type])) {
                if ($field_type == 'P') {
                    $currency = !empty($vals[2]) ? $vals[2] : CART_PRIMARY_CURRENCY;
                    if ($currency != CART_PRIMARY_CURRENCY) {
                        $coef = Registry::get('currencies.' . $currency . '.coefficient');
                        $decimals = Registry::get('currencies.' . CART_PRIMARY_CURRENCY . '.decimals');
                        $vals[0] = round(floatval($vals[0]) * floatval($coef), $decimals);
                        $vals[1] = round(floatval($vals[1]) * floatval($coef), $decimals);
                    }
                }

                $structure = $filter_fields[$field_type];
                $params["$structure[db_field]_from"] = $vals[0];
                $params["$structure[db_field]_to"] = $vals[1];
            }
        }
    }

    // Checkbox features
    if (!empty($params['ch_filters']) && !fn_is_empty($params['ch_filters'])) {
        foreach ($params['ch_filters'] as $k => $v) {
            // Product field filter
            if (is_string($k) == true && !empty($v) && $structure = $filter_fields[$k]) {
                $condition .= db_quote(" AND $structure[table].$structure[db_field] IN (?a)", ($v == 'A' ? array('Y', 'N') : $v));
            // Feature filter
            } elseif (!empty($v)) {
                $fid = intval($k);
                $join .= db_quote(" LEFT JOIN ?:product_features_values as ch_features_$fid ON ch_features_$fid.product_id = products.product_id AND ch_features_$fid.lang_code = ?s", $lang_code);
                $condition .= db_quote(" AND ch_features_$fid.feature_id = ?i AND ch_features_$fid.value IN (?a)", $fid, ($v == 'A' ? array('Y', 'N') : $v));
            }
        }
    }

    // Text features
    if (!empty($params['tx_features'])) {
        foreach ($params['tx_features'] as $k => $v) {
            if (fn_string_not_empty($v)) {
                $fid = intval($k);
                $join .= " LEFT JOIN ?:product_features_values as tx_features_$fid ON tx_features_$fid.product_id = products.product_id";
                $condition .= db_quote(" AND tx_features_$fid.value LIKE ?l AND tx_features_$fid.lang_code = ?s", "%" . trim($v) . "%", $lang_code);
            }
        }
    }

    $total = 0;
    fn_set_hook('get_products_before_select', $params, $join, $condition, $u_condition, $inventory_condition, $sortings, $total, $items_per_page, $lang_code, $having);

    //
    // [/Advanced filters]
    //

    $feature_search_condition = '';
    if (!empty($params['feature'])) {
        // Extended search by product fields
        $_cond = array();
        $total_hits = 0;
        foreach ($params['feature'] as $f_id) {
            if (!empty($f_val)) {
                $total_hits++;
                $_cond[] = db_quote("(?:product_features_values.feature_id = ?i)", $f_id);
            }
        }

        $params['extend'][] = 'categories';
        if (!empty($_cond)) {
            $cache_feature_search = db_get_fields("SELECT product_id, COUNT(product_id) as cnt FROM ?:product_features_values WHERE (" . implode(' OR ', $_cond) . ") GROUP BY product_id HAVING cnt = $total_hits");
            $feature_search_condition .= db_quote(" AND products_categories.product_id IN (?n)", $cache_feature_search);
        }
    }

    // Filter by category ID
    if (!empty($params['cid'])) {
        $cids = is_array($params['cid']) ? $params['cid'] : explode(',', $params['cid']);

        if (isset($params['subcats']) && $params['subcats'] == 'Y') {
            $_ids = db_get_fields(
                "SELECT a.category_id"."
                 FROM ?:categories as a"."
                 LEFT JOIN ?:categories as b"."
                 ON b.category_id IN (?n)"."
                 WHERE a.id_path LIKE CONCAT(b.id_path, '/%')",
                $cids
            );

            $cids = fn_array_merge($cids, $_ids, false);
        }

        $params['extend'][] = 'categories_filter';
        $condition .= db_quote(" AND categories_filter.category_id IN (?n)", $cids);
    }

    // If we need to get the products by IDs and no IDs passed, don't search anything
    if (!empty($params['force_get_by_ids']) && empty($params['pid']) && empty($params['product_id'])) {
        return array(array(), $params, 0);
    }

    // Product ID search condition for SQL query
    if (!empty($params['pid'])) {
        $u_condition .= db_quote($union_condition . ' products.product_id IN (?n)', $params['pid']);
    }

    // Exclude products from search results
    if (!empty($params['exclude_pid'])) {
        $condition .= db_quote(' AND products.product_id NOT IN (?n)', $params['exclude_pid']);
    }

    // Search by feature comparison flag
    if (!empty($params['feature_comparison'])) {
        $condition .= db_quote(' AND products.feature_comparison = ?s', $params['feature_comparison']);
    }

    // Search products by localization
    $condition .= fn_get_localizations_condition('products.localization', true);

    $company_condition = '';

    if (fn_allowed_for('MULTIVENDOR')) {
        if ($params['area'] == 'C') {
            $company_condition .= " AND companies.status = 'A' ";
            $params['extend'][] = 'companies';
        } else {
            $company_condition .= fn_get_company_condition('products.company_id');
        }
    } else {
        $cat_company_condition = '';
        if (Registry::get('runtime.company_id')) {
            $params['extend'][] = 'categories';
            $cat_company_condition .= fn_get_company_condition('?:categories.company_id');
        } elseif (!empty($params['company_ids'])) {
            $params['extend'][] = 'categories';
            $cat_company_condition .= db_quote(' AND ?:categories.company_id IN (?a)', explode(',', $params['company_ids']));
        }
        $company_condition .= $cat_company_condition;

        if (in_array('categories_filter', $params['extend']) && !empty($cat_company_condition)) {
            $company_condition .= str_replace('?:categories', 'categories_filter', $cat_company_condition);
        }
    }

    $condition .= $company_condition;

    if (!fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && isset($params['company_id'])) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }
    if (isset($params['company_id']) && $params['company_id'] != '') {
        $condition .= db_quote(' AND products.company_id = ?i ', $params['company_id']);
    }

    if (!empty($params['filter_params'])) {
        foreach ($params['filter_params'] as $field => $f_vals) {
            $condition .= db_quote(' AND products.' . $field . ' IN (?a) ', $f_vals);
        }
    }

    if (isset($params['price_from']) && fn_is_numeric($params['price_from'])) {
        $condition .= db_quote(' AND prices.price >= ?d', fn_convert_price(trim($params['price_from'])));
        $params['extend'][] = 'prices2';
    }

    if (isset($params['price_to']) && fn_is_numeric($params['price_to'])) {
        $condition .= db_quote(' AND prices.price <= ?d', fn_convert_price(trim($params['price_to'])));
        $params['extend'][] = 'prices2';
    }

    if (isset($params['weight_from']) && fn_is_numeric($params['weight_from'])) {
        $condition .= db_quote(' AND products.weight >= ?d', fn_convert_weight(trim($params['weight_from'])));
    }

    if (isset($params['weight_to']) && fn_is_numeric($params['weight_to'])) {
        $condition .= db_quote(' AND products.weight <= ?d', fn_convert_weight(trim($params['weight_to'])));
    }

    // search specific inventory status
    if (!empty($params['tracking'])) {
        $condition .= db_quote(' AND products.tracking IN(?a)', $params['tracking']);
    }

    if (isset($params['amount_from']) && fn_is_numeric($params['amount_from'])) {
        $condition .= db_quote(
            " AND IF(products.tracking = ?s, inventory.amount >= ?i, products.amount >= ?i)",
            ProductTracking::TRACK_WITH_OPTIONS,
            $params['amount_from'],
            $params['amount_from']
        );
        $inventory_condition .= db_quote(' AND inventory.amount >= ?i', $params['amount_from']);
    }

    if (isset($params['amount_to']) && fn_is_numeric($params['amount_to'])) {
        $condition .= db_quote(
            " AND IF(products.tracking = ?s, inventory.amount <= ?i, products.amount <= ?i)",
            ProductTracking::TRACK_WITH_OPTIONS,
            $params['amount_to'],
            $params['amount_to']
        );
        $inventory_condition .= db_quote(' AND inventory.amount <= ?i', $params['amount_to']);
    }

    // Cut off out of stock products
    if (Registry::get('settings.General.inventory_tracking') == 'Y' && // FIXME? Registry in model
        Registry::get('settings.General.show_out_of_stock_products') == 'N' &&
        $params['area'] == 'C'
    ) {
        $condition .= db_quote(
            " AND (CASE products.tracking
                WHEN ?s THEN inventory.amount > 0
                WHEN ?s THEN products.amount > 0
                WHEN ?s THEN 1
            END)",
            ProductTracking::TRACK_WITH_OPTIONS,
            ProductTracking::TRACK_WITHOUT_OPTIONS,
            ProductTracking::DO_NOT_TRACK
        );
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND products.status IN (?a)', $params['status']);
    }

    if (!empty($params['shipping_freight_from'])) {
        $condition .= db_quote(' AND products.shipping_freight >= ?d', $params['shipping_freight_from']);
    }

    if (!empty($params['shipping_freight_to'])) {
        $condition .= db_quote(' AND products.shipping_freight <= ?d', $params['shipping_freight_to']);
    }

    if (!empty($params['free_shipping'])) {
        $condition .= db_quote(' AND products.free_shipping = ?s', $params['free_shipping']);
    }

    if (!empty($params['downloadable'])) {
        $condition .= db_quote(' AND products.is_edp = ?s', $params['downloadable']);
    }

    if (isset($params['pcode']) && fn_string_not_empty($params['pcode'])) {
        $pcode = trim($params['pcode']);
        $fields['combination'] = 'inventory.combination';
        $u_condition .= db_quote(" $union_condition (inventory.product_code LIKE ?l OR products.product_code LIKE ?l)", "%$pcode%", "%$pcode%");
        $inventory_condition .= db_quote(" AND inventory.product_code LIKE ?l", "%$pcode%");
    }

    if ((isset($params['amount_to']) && fn_is_numeric($params['amount_to'])) || (isset($params['amount_from']) && fn_is_numeric($params['amount_from'])) || !empty($params['pcode']) || (Registry::get('settings.General.inventory_tracking') == 'Y' && Registry::get('settings.General.show_out_of_stock_products') == 'N' && $params['area'] == 'C')) {
        $join .= " LEFT JOIN ?:product_options_inventory as inventory ON inventory.product_id = products.product_id $inventory_condition";
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (products.timestamp >= ?i AND products.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['item_ids'])) {
        $condition .= db_quote(" AND products.product_id IN (?n)", explode(',', $params['item_ids']));
    }

    if (isset($params['popularity_from']) && fn_is_numeric($params['popularity_from'])) {
        $params['extend'][] = 'popularity';
        $condition .= db_quote(' AND popularity.total >= ?i', $params['popularity_from']);
    }

    if (isset($params['popularity_to']) && fn_is_numeric($params['popularity_to'])) {
        $params['extend'][] = 'popularity';
        $condition .= db_quote(' AND popularity.total <= ?i', $params['popularity_to']);
    }

    if (!empty($params['order_ids'])) {
        $arr = (strpos($params['order_ids'], ',') !== false || !is_array($params['order_ids'])) ? explode(',', $params['order_ids']) : $params['order_ids'];

        $condition .= db_quote(" AND ?:order_details.order_id IN (?n)", $arr);

        $join .= " LEFT JOIN ?:order_details ON ?:order_details.product_id = products.product_id";
    }

    $limit = '';
    $group_by = 'products.product_id';
    // Show enabled products
    $_p_statuses = array('A');
    $condition .= ($params['area'] == 'C') ? ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'products.usergroup_ids', true) . ')' . db_quote(' AND products.status IN (?a)', $_p_statuses) : '';

    // -- JOINS --
    if (in_array('product_name', $params['extend'])) {
        $fields['product'] = 'descr1.product as product';
        $join .= db_quote(" LEFT JOIN ?:product_descriptions as descr1 ON descr1.product_id = products.product_id AND descr1.lang_code = ?s ", $lang_code);
    }

    // get prices
    $price_condition = '';
    if (in_array('prices', $params['extend'])) {
        $fields['price'] = 'MIN(IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100)) as price';
        $join .= " LEFT JOIN ?:product_prices as prices ON prices.product_id = products.product_id AND prices.lower_limit = 1";
        $price_condition = db_quote(' AND prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        $condition .= $price_condition;
    }

    // get prices for search by price
    if (in_array('prices2', $params['extend'])) {
        $price_usergroup_cond_2 = db_quote(' AND prices_2.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
        $join .= " LEFT JOIN ?:product_prices as prices_2 ON prices.product_id = prices_2.product_id AND prices_2.lower_limit = 1 AND prices_2.price < prices.price " . $price_usergroup_cond_2;
        $condition .= ' AND prices_2.price IS NULL';
        $price_condition .= ' AND prices_2.price IS NULL';
    }

    // get short & full description
    if (in_array('search_words', $params['extend'])) {
        $fields['search_words'] = 'descr1.search_words';
    }

    // get short & full description
    if (in_array('description', $params['extend'])) {
        $fields['short_description'] = 'descr1.short_description';

        if (in_array('full_description', $params['extend'])) {
            $fields['full_description'] = "descr1.full_description";
        } else {
            $fields['full_description'] = "IF(descr1.short_description = '', descr1.full_description, '') as full_description";
        }
    }

    // get companies
    $companies_join = db_quote(" LEFT JOIN ?:companies AS companies ON companies.company_id = products.company_id ");
    if (in_array('companies', $params['extend'])) {
        $fields['company_name'] = 'companies.company as company_name';
        $join .= $companies_join;
    }

    // for compatibility
    if (in_array('category_ids', $params['extend'])) {
        $params['extend'][] = 'categories';
    }

    // get categories
    $_c_statuses = array('A' , 'H');// Show enabled categories
    $skip_checking_usergroup_permissions = fn_is_preview_action($auth, $params);

    if ($skip_checking_usergroup_permissions) {
        $category_avail_cond = '';
    } else {
        $category_avail_cond = ($params['area'] == 'C') ? ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ')' : '';
    }
    $category_avail_cond .= ($params['area'] == 'C') ? db_quote(" AND ?:categories.status IN (?a) ", $_c_statuses) : '';
    $categories_join = " INNER JOIN ?:products_categories as products_categories ON products_categories.product_id = products.product_id INNER JOIN ?:categories ON ?:categories.category_id = products_categories.category_id $category_avail_cond $feature_search_condition";

    if (!empty($params['order_ids'])) {
        // Avoid duplicating by sub-categories
        $condition .= db_quote(' AND products_categories.link_type = ?s', 'M');
    }

    if (in_array('categories', $params['extend'])) {
        $fields['category_ids'] = "GROUP_CONCAT(IF(products_categories.link_type = 'M', CONCAT(products_categories.category_id, 'M'), products_categories.category_id)) as category_ids";
        $fields['categories_position'] = 'products_categories.position';
        $join .= $categories_join;

        $condition .= fn_get_localizations_condition('?:categories.localization', true);
    }

    if (in_array('categories_filter', $params['extend'])) {
        $category_filter_avail_cond = str_replace('?:categories', 'categories_filter', $category_avail_cond);
        $join .= " INNER JOIN ?:products_categories as products_categories_filter ON products_categories_filter.product_id = products.product_id INNER JOIN ?:categories AS categories_filter ON categories_filter.category_id = products_categories_filter.category_id $category_filter_avail_cond ";
        $condition .= fn_get_localizations_condition('categories_filter.localization', true);
    }

    // get popularity
    $popularity_join = db_quote(" LEFT JOIN ?:product_popularity as popularity ON popularity.product_id = products.product_id");
    if (in_array('popularity', $params['extend'])) {
        $fields['popularity'] = 'popularity.total as popularity';
        $join .= $popularity_join;
    }

    if (!empty($params['get_subscribers'])) {
        $join .= " LEFT JOIN ?:product_subscriptions as product_subscriptions ON product_subscriptions.product_id = products.product_id";
    }

    //  -- \JOINs --

    if (!empty($u_condition)) {
        $condition .= " $union_condition ((" . ($union_condition == ' OR ' ? '0 ' : '1 ') . $u_condition . ')' . $company_condition . $price_condition . ')';
    }

    /**
     * Changes additional params for selecting products
     *
     * @param array  $params    Product search params
     * @param array  $fields    List of fields for retrieving
     * @param array  $sortings  Sorting fields
     * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join      String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $sorting   String containing the SQL-query ORDER BY clause
     * @param string $group_by  String containing the SQL-query GROUP BY field
     * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param array  $having    HAVING condition
     */
    fn_set_hook('get_products', $params, $fields, $sortings, $condition, $join, $sorting, $group_by, $lang_code, $having);

    // -- SORTINGS --
    if (empty($params['sort_by']) || empty($sortings[$params['sort_by']])) {
        $params = array_merge($params, fn_get_default_products_sorting());
        if (empty($sortings[$params['sort_by']])) {
            $_products_sortings = fn_get_products_sorting();
            $params['sort_by'] = key($_products_sortings);
        }
    }

    $default_sorting = fn_get_products_sorting();

    if ($params['sort_by'] == 'popularity' && !in_array('popularity', $params['extend'])) {
        $join .= $popularity_join;
    }

    if ($params['sort_by'] == 'company' && !in_array('companies', $params['extend'])) {
        $join .= $companies_join;
    }

    if (empty($params['sort_order'])) {
        if (!empty($default_sorting[$params['sort_by']]['default_order'])) {
            $params['sort_order'] = $default_sorting[$params['sort_by']]['default_order'];
        } else {
            $params['sort_order'] = 'asc';
        }
    }

    $sorting = db_sort($params, $sortings);

    if (fn_allowed_for('ULTIMATE')) {
        if (in_array('sharing', $params['extend'])) {
            $fields['is_shared_product'] = "IF(COUNT(IF(?:categories.company_id = products.company_id, NULL, ?:categories.company_id)), 'Y', 'N') as is_shared_product";
            if (strpos($join, $categories_join) === false) {
                $join .= $categories_join;
            }
        }
    }

    // -- \SORTINGS --

    // Used for View cascading
    if (!empty($params['get_query'])) {
        return "SELECT products.product_id FROM ?:products as products $join WHERE 1 $condition GROUP BY products.product_id";
    }

    // Used for Extended search
    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition);
    }

    if (!empty($params['limit'])) {
        $limit = db_quote(" LIMIT 0, ?i", $params['limit']);
    } elseif (!empty($params['items_per_page'])) {
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $calc_found_rows = '';
    if (empty($total)) {
        $calc_found_rows = 'SQL_CALC_FOUND_ROWS';
    }

    if (!empty($having)) {
        $having = ' HAVING ' . implode(' AND ', $having);
    } else {
        $having = '';
    }

    $products = db_get_array("SELECT $calc_found_rows " . implode(', ', $fields) . " FROM ?:products as products $join WHERE 1 $condition GROUP BY $group_by $having $sorting $limit");

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = !empty($total)? $total : db_get_found_rows();
    } else {
        $params['total_items'] = count($products);
    }

    // Post processing
    if (in_array('categories', $params['extend'])) {
        foreach ($products as $k => $v) {
            list($products[$k]['category_ids'], $products[$k]['main_category']) = fn_convert_categories($v['category_ids']);
        }
    }

    if (!empty($params['get_frontend_urls'])) {
        foreach ($products as &$product) {
            $product['url'] = fn_url('products.view?product_id=' . $product['product_id'], 'C');
        }
    }

    if (!empty($params['item_ids'])) {
        $products = fn_sort_by_ids($products, explode(',', $params['item_ids']));
    }
    if (!empty($params['pid']) && !empty($params['apply_limit']) && $params['apply_limit']) {
        $products = fn_sort_by_ids($products, $params['pid']);
    }

    /**
     * Changes selected products
     *
     * @param array  $products  Array of products
     * @param array  $params    Product search params
     * @param string $lang_code Language code
     */
    fn_set_hook('get_products_post', $products, $params, $lang_code);

    LastView::instance()->processResults('products', $products, $params);

    return array($products, $params);
}

function fn_sort_by_ids($items, $ids, $field = 'product_id')
{
    $tmp = array();

    foreach ($items as $k => $item) {
        foreach ($ids as $key => $item_id) {
            if ($item_id == $item[$field]) {
                $tmp[$key] = $item;
                break;
            }
        }
    }

    ksort($tmp);

    return $tmp;
}

function fn_convert_categories($category_ids)
{
    $c_ids = explode(',', $category_ids);
    $categories = array();
    $main_category = 0;
    foreach ($c_ids as $v) {
        if (strpos($v, 'M') !== false) {
            $main_category = intval($v);
        }
        if (!in_array(intval($v), $categories)) {
            $categories[] = intval($v);
        }
    }

    if (empty($main_category)) {
        $main_category = reset($categories);
    }

    return array($categories, $main_category);
}

/**
 * Updates product option
 *
 * @param array $option_data option data array
 * @param int $option_id option ID (empty if we're adding the option)
 * @param string $lang_code language code to add/update option for
 * @return int ID of the added/updated option
 */
function fn_update_product_option($option_data, $option_id = 0, $lang_code = DESCR_SL)
{
    /**
     * Changes parameters before update option data
     *
     * @param array  $option_data Option data
     * @param int    $option_id   Option identifier
     * @param string $lang_code   Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('update_product_option_pre', $option_data, $option_id, $lang_code);

    // Add option
    if (empty($option_id)) {

        if (empty($option_data['product_id'])) {
            $option_data['product_id'] = 0;
        }

        $option_data['option_id'] = $option_id = db_query('INSERT INTO ?:product_options ?e', $option_data);

        foreach (fn_get_translation_languages() as $option_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:product_options_descriptions ?e", $option_data);
        }

        $create = true;
    // Update option
    } else {

        // if option inventory changed from Y to N, we should clear option combinations
        if (!empty($option_data['product_id']) && !empty($option_data['inventory']) && $option_data['inventory'] == 'N') {
            $condition = fn_get_company_condition('?:product_options.company_id');
            $old_option_inventory = db_get_field("SELECT inventory FROM ?:product_options WHERE option_id = ?i $condition", $option_id);
            if ($old_option_inventory == 'Y') {
                $inventory_filled = db_get_field('SELECT COUNT(*) FROM ?:product_options_inventory WHERE product_id = ?i', $option_data['product_id']);
                if ($inventory_filled) {
                    fn_delete_product_option_combinations($option_data['product_id']);
                }
            }
        }

        if (fn_allowed_for('ULTIMATE') && !empty($option_data['product_id']) && fn_ult_is_shared_product($option_data['product_id']) == 'Y') {
            $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $option_data['product_id']);
            $option_id = fn_ult_update_shared_product_option($option_data, $option_id, Registry::ifGet('runtime.company_id', $product_company_id), $lang_code);

            if (Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $product_company_id) {
                $deleted_variants = array();
                fn_set_hook('update_product_option_post', $option_data, $option_id, $deleted_variants, $lang_code);

                return $option_id;
            }
        }

        db_query("UPDATE ?:product_options SET ?u WHERE option_id = ?i", $option_data, $option_id);
        db_query("UPDATE ?:product_options_descriptions SET ?u WHERE option_id = ?i AND lang_code = ?s", $option_data, $option_id, $lang_code);
    }

    if (fn_allowed_for('ULTIMATE')) {
        // options of shared product under the shared store hasn't a company_id. No necessary for updating.
        if (!empty($option_data['company_id'])) {
            fn_ult_update_share_object($option_id, 'product_options', $option_data['company_id']);
        }

        if (!empty($option_data['product_id'])) {
            fn_ult_share_product_option($option_id, $option_data['product_id']);
        }
    }

    if (!empty($option_data['variants'])) {
        $var_ids = array();

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

        $variant_images = array();
        foreach ($option_data['variants'] as $k => $v) {
            if ((!isset($v['variant_name']) || $v['variant_name'] == '') && $option_data['option_type'] != 'C') {
                continue;
            }

            // Update product options variants
            if (isset($v['modifier'])) {
                $v['modifier'] = floatval($v['modifier']);
                if (floatval($v['modifier']) > 0) {
                    $v['modifier'] = '+' . $v['modifier'];
                }
            }

            if (isset($v['weight_modifier'])) {
                $v['weight_modifier'] = floatval($v['weight_modifier']);
                if (floatval($v['weight_modifier']) > 0) {
                    $v['weight_modifier'] = '+' . $v['weight_modifier'];
                }
            }

            $v['option_id'] = $option_id;

            if (empty($v['variant_id']) || (!empty($v['variant_id']) && !db_get_field("SELECT variant_id FROM ?:product_option_variants WHERE variant_id = ?i", $v['variant_id']))) {
                $v['variant_id'] = db_query("INSERT INTO ?:product_option_variants ?e", $v);
                foreach (fn_get_translation_languages() as $v['lang_code'] => $_v) {
                    db_query("INSERT INTO ?:product_option_variants_descriptions ?e", $v);
                }
            } else {
                db_query("UPDATE ?:product_option_variants SET ?u WHERE variant_id = ?i", $v, $v['variant_id']);
                db_query("UPDATE ?:product_option_variants_descriptions SET ?u WHERE variant_id = ?i AND lang_code = ?s", $v, $v['variant_id'], $lang_code);
            }

            $var_ids[] = $v['variant_id'];

            if ($option_data['option_type'] == 'C') {
                fn_delete_image_pairs($v['variant_id'], 'variant_image'); // force deletion of variant image for "checkbox" option
            } else {
                $variant_images[$k] = $v['variant_id'];
            }
        }

        if ($option_data['option_type'] != 'C' && !empty($variant_images)) {
            fn_attach_image_pairs('variant_image', 'variant_image', 0, $lang_code, $variant_images);
        }

        // Delete obsolete variants
        $condition = !empty($var_ids) ? db_quote('AND variant_id NOT IN (?n)', $var_ids) : '';
        $deleted_variants = db_get_fields("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i $condition", $option_id, $var_ids);
        if (!empty($deleted_variants)) {
            db_query("DELETE FROM ?:product_option_variants WHERE variant_id IN (?n)", $deleted_variants);
            db_query("DELETE FROM ?:product_option_variants_descriptions WHERE variant_id IN (?n)", $deleted_variants);
            foreach ($deleted_variants as $v_id) {
                fn_delete_image_pairs($v_id, 'variant_image');
            }
        }
    }
    if (!fn_allowed_for('ULTIMATE:FREE')) {
        // Rebuild exceptions
        if (!empty($create) && !empty($option_data['product_id'])) {
            fn_update_exceptions($option_data['product_id']);
        }
    }

    /**
     * Update product option (running after fn_update_product_option() function)
     *
     * @param array  $option_data      Array with option data
     * @param int    $option_id        Option identifier
     * @param array  $deleted_variants Array with deleted variants ids
     * @param string $lang_code        Language code to add/update option for
     */
    fn_set_hook('update_product_option_post', $option_data, $option_id, $deleted_variants, $lang_code);

    return $option_id;
}

function fn_convert_weight($weight)
{
    /**
     * Change weight before converting
     *
     * @param float $weight Weight for converting
     */
    fn_set_hook('convert_weight_pre', $weight);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (Registry::get('config.localization.weight_unit')) {
            $g = Registry::get('settings.General.weight_symbol_grams');
            $weight = $weight * Registry::get('config.localization.weight_unit') / $g;
        }
    }
    $result = sprintf('%01.2f', $weight);

    /**
     * Change the converted weight
     *
     * @param float $result Converted weight
     * @param float $weight Weight for converting
     */
    fn_set_hook('convert_weight_post', $result, $weight);

    return $result;
}

function fn_convert_price($price)
{
    /**
     * Change price before converting
     *
     * @param float $price Price for converting
     */
    fn_set_hook('convert_price_pre', $price);

    $currencies = Registry::get('currencies');
    $result = $price * $currencies[CART_PRIMARY_CURRENCY]['coefficient'];

    /**
     * Change the converted price
     *
     * @param float $result Converted price
     * @param float $price  Price for converting
     */
    fn_set_hook('convert_price_post', $result, $price);

    return $result;
}

function fn_get_products_sorting()
{
    $sorting = array(
        'null' => array('description' => __('none'), 'default_order' => 'asc', 'desc' => false),
        'timestamp' => array('description' => __('date'), 'default_order' => 'desc'),
        'position' => array('description' => __('default'), 'default_order' => 'asc'),
        'product' => array('description' => __('name'), 'default_order' => 'asc'),
        'price' => array('description' => __('price'), 'default_order' => 'asc'),
        'popularity' => array('description' => __('popularity'), 'default_order' => 'desc')
    );

    /**
     * Change products sortings
     *
     * @param array   $sorting     Sortings
     * @param boolean $simple_mode Flag that defines if products sortings should be returned as simple titles list
     */
    fn_set_hook('products_sorting', $sorting, $simple_mode);

    return $sorting;
}

function fn_get_products_sorting_orders()
{
    $result = array('asc', 'desc');

    /**
     * Change products sorting orders
     *
     * @param array $result Sorting orders
     */
    fn_set_hook('get_products_sorting_orders', $result);

    return $result;
}

function fn_get_products_views($simple_mode = true, $active = false)
{
    /**
     * Change params for getting product views
     *
     * @param boolean $simple_mode Flag that defines is product views should be returned in simple mode
     * @param boolean $active      Flag that defines if only active views should be returned
     */
    fn_set_hook('get_products_views_pre', $simple_mode, $active);

    $active_layouts = Registry::get('settings.Appearance.default_products_view_templates');
    if (!is_array($active_layouts)) {
        parse_str($active_layouts, $active_layouts);
    }

    if (!array_key_exists(Registry::get('settings.Appearance.default_products_view'), $active_layouts)) {
        $active_layouts[Registry::get('settings.Appearance.default_products_view')] = 'Y';
    }

    /*if (Registry::isExist('products_views') == true && AREA != 'A') {
        $products_views = Registry::get('products_views');

        foreach ($products_views as &$view) {
            $view['title'] = __($view['title']);
        }

        if ($simple_mode) {
            $products_views = Registry::get('products_views');

            foreach ($products_views as $key => $value) {
                $products_views[$key] = $value['title'];
            }
        }

        if ($active) {
            $products_views = array_intersect_key($products_views, $active_layouts);
        }

        return $products_views;
    }*/

    $products_views = array();

    list($theme_path, $theme_name) = fn_get_customer_layout_theme_path();

    // Get all available product_list_templates dirs
    $templates_path[] = $theme_path . '/templates/blocks/product_list_templates';

    foreach ((array) Registry::get('addons') as $addon_name => $data) {
        if ($data['status'] == 'A') {
            if (is_dir($theme_path . '/templates/addons/' . $addon_name . '/blocks/product_list_templates')) {
                $templates_path[] = $theme_path . '/templates/addons/' . $addon_name . '/blocks/product_list_templates';
            }
        }
    }

    // Scan received directories and fill the "views" array
    foreach ($templates_path as &$path) {
        $view_templates = fn_get_dir_contents($path, false, true, 'tpl');

        if (!empty($view_templates)) {
            foreach ($view_templates as &$file) {
                if ($file != '.' && $file != '..') {
                    preg_match("/(.*" . fn_basename($theme_name) . "\/templates\/)(.*)/", $path, $matches);

                    $_path = $matches[2]. '/' . $file;

                    // Check if the template has inner description (like a "block manager")
                    $tempalte_description = fn_get_file_description($path . '/' . $file, 'template-description', true);

                    $_title = substr($file, 0, -4);

                    $products_views[$_title] = array(
                        'template' => $_path,
                        'title' => empty($tempalte_description) ? $_title : $tempalte_description,
                        'active' => array_key_exists($_title, $active_layouts)
                    );
                }
            }
        }
    }

    //Registry::set('products_views',  $products_views);

    foreach ($products_views as &$view) {
        $view['title'] = __($view['title']);
    }

    if ($simple_mode) {
        foreach ($products_views as $key => $value) {
            $products_views[$key] = $value['title'];
        }
    }

    if ($active) {
        $products_views = array_intersect_key($products_views, $active_layouts);
    }

    /**
     * Change product views
     *
     * @param array   $products_views Array of products views
     * @param boolean $simple_mode    Flag that defines is product views should be returned in simple mode
     * @param boolean $active         Flag that defines if only active views should be returned
     */
    fn_set_hook('get_products_views_post', $products_views, $simple_mode, $active);

    return $products_views;
}

function fn_get_products_layout($params)
{
    /**
     * Change params for getting products layout
     *
     * @param array $params Params for getting products layout
     */
    fn_set_hook('get_products_layout_pre', $params);

    if (!isset($_SESSION['products_layout'])) {
        $_SESSION['products_layout'] = Registry::get('settings.Appearance.save_selected_view') == 'Y' ? array() : '';
    }

    $active_layouts = fn_get_products_views(false, true);
    $default_layout = Registry::get('settings.Appearance.default_products_view');

    if (!empty($params['category_id'])) {
        $_layout = db_get_row("SELECT default_layout, selected_layouts FROM ?:categories WHERE category_id = ?i", $params['category_id']);
        $category_default_layout = $_layout['default_layout'];
        $category_layouts = unserialize($_layout['selected_layouts']);
        if (!empty($category_layouts)) {
            if (!empty($category_default_layout)) {
                $default_layout = $category_default_layout;
            }
            $active_layouts = $category_layouts;
        }
        $ext_id = $params['category_id'];
    } else {
        $ext_id = 'search';
    }

    if (!empty($params['layout'])) {
        $layout = $params['layout'];
    } elseif (Registry::get('settings.Appearance.save_selected_view') == 'Y' && !empty($_SESSION['products_layout'][$ext_id])) {
        $layout = $_SESSION['products_layout'][$ext_id];
    } elseif (Registry::get('settings.Appearance.save_selected_view') == 'N' && !empty($_SESSION['products_layout'])) {
        $layout = $_SESSION['products_layout'];
    }

    $selected_layout = (!empty($layout) && !empty($active_layouts[$layout])) ? $layout : $default_layout;

    /**
     * Change selected layout
     *
     * @param array $selected_layout Selected layout
     * @param array $params          Params for getting products layout
     */
    fn_set_hook('get_products_layout_post', $selected_layout, $params);

    if (!empty($params['layout']) && $params['layout'] == $selected_layout) {
        if (Registry::get('settings.Appearance.save_selected_view') == 'Y') {
            if (!is_array($_SESSION['products_layout'])) {
                $_SESSION['products_layout'] = array();
            }
            $_SESSION['products_layout'][$ext_id] = $selected_layout;
        } else {
            $_SESSION['products_layout'] = $selected_layout;
        }
    }

    return $selected_layout;
}

function fn_get_categories_list($category_ids, $lang_code = CART_LANGUAGE)
{
    /**
     * Change params for getting categories list
     *
     * @param array  $category_ids Category identifier
     * @param string $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_categories_list_pre', $category_ids, $lang_code);

    static $max_categories = 10;
    $c_names = array();
    if (!empty($category_ids)) {
        $c_ids = fn_explode(',', $category_ids);
        $tr_c_ids = array_slice($c_ids, 0, $max_categories);
        $c_names = fn_get_category_name($tr_c_ids, $lang_code);
        if (sizeof($tr_c_ids) < sizeof($c_ids)) {
            $c_names[] = '... (' . sizeof($c_ids) . ')';
        }
    } else {
        $c_names[] = __('all_categories');
    }

    /**
     * Change categories list
     *
     * @param array  $c_names      Categories names list
     * @param array  $category_ids Category identifier
     * @param string $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('get_categories_list_post', $c_names, $category_ids, $lang_code);

    return $c_names;
}

function fn_get_allowed_options_combination($options, $variants, $string, $iteration, $exceptions, $inventory_combinations)
{
    /**
     * Changes parameters for getting allowed options combination
     *
     * @param array $options                Product options
     * @param array $variants               Product variants
     * @param array $string                 Array of combinations values
     * @param int   $iteration              Iteration level
     * @param array $exceptions             Options exceptions
     * @param array $inventory_combinations Inventory combinations
     */
    fn_set_hook('get_allowed_options_combination_pre', $options, $variants, $string, $iteration, $exceptions, $inventory_combinations);

    static $result = array();
    $combinations = array();
    foreach ($variants[$iteration] as $variant_id) {
        if (count($options) - 1 > $iteration) {
            $string[$iteration][$options[$iteration]] = $variant_id;
            list($_c, $is_result) = fn_get_allowed_options_combination($options, $variants, $string, $iteration + 1, $exceptions, $inventory_combinations);
            if ($is_result) {
                return array($_c, $is_result);
            }

            $combinations = array_merge($combinations, $_c);
            unset($string[$iteration]);
        } else {
            $_combination = array();
            if (!empty($string)) {
                foreach ($string as $val) {
                    foreach ($val as $opt => $var) {
                        $_combination[$opt] = $var;
                    }
                }
            }
            $_combination[$options[$iteration]] = $variant_id;
            $combinations[] = $_combination;

            foreach ($combinations as $combination) {
                $allowed = true;
                foreach ($exceptions as $exception) {
                    $res = array_diff($exception, $combination);

                    if (empty($res)) {
                        $allowed = false;
                        break;

                    } else {
                        foreach ($res as $option_id => $variant_id) {
                            if ($variant_id == -1) {
                                unset($res[$option_id]);
                            }
                        }

                        if (empty($res)) {
                            $allowed = false;
                            break;
                        }
                    }
                }

                if ($allowed) {
                    $result = $combination;

                    if (empty($inventory_combinations)) {
                        return array($result, true);
                    } else {
                        foreach ($inventory_combinations as $_icombination) {
                            $_res = array_diff($_icombination, $combination);
                            if (empty($_res)) {
                                return array($result, true);
                            }
                        }
                    }
                }
            }

            $combinations = array();
        }
    }

    if ($iteration == 0) {
        return array($result, true);
    } else {
        return array($combinations, false);
    }
}

function fn_apply_options_rules($product)
{
    /**
     * Changes product data before applying product options rules
     *
     * @param array $product Product data
     */
    fn_set_hook('apply_options_rules_pre', $product);

    /*	Options type:
            P - simultaneous/parallel
            S - sequential
    */
    // Check for the options and exceptions types
    if (!isset($product['options_type']) || !isset($product['exceptions_type'])) {
        $product = array_merge($product, db_get_row('SELECT options_type, exceptions_type FROM ?:products WHERE product_id = ?i', $product['product_id']));
    }

    // Get the selected options or get the default options
    $product['selected_options'] = empty($product['selected_options']) ? array() : $product['selected_options'];
    $product['options_update'] = ($product['options_type'] == 'S') ? true : false;

    // Conver the selected options text to the utf8 format
    if (!empty($product['product_options'])) {
        foreach ($product['product_options'] as $id => $option) {
            if (!empty($option['value'])) {
                $product['product_options'][$id]['value'] = fn_unicode_to_utf8($option['value']);
            }
            if (!empty($product['selected_options'][$option['option_id']])) {
                $product['selected_options'][$option['option_id']] = fn_unicode_to_utf8($product['selected_options'][$option['option_id']]);
            }
        }
    }

    $selected_options = &$product['selected_options'];
    $changed_option = empty($product['changed_option']) ? true : false;

    $simultaneous = array();
    $next = 0;

    foreach ($product['product_options'] as $_id => $option) {
        if (!in_array($option['option_type'], array('I', 'T', 'F'))) {
            $simultaneous[$next] = $option['option_id'];
            $next = $option['option_id'];
        }

        if (!empty($option['value'])) {
            $selected_options[$option['option_id']] = $option['value'];
        }

        if (!$changed_option && $product['changed_option'] == $option['option_id']) {
            $changed_option = true;
        }

        if (!empty($selected_options[$option['option_id']]) && ($selected_options[$option['option_id']] == 'checked' || $selected_options[$option['option_id']] == 'unchecked') && $option['option_type'] == 'C') {
            foreach ($option['variants'] as $variant) {
                if (($variant['position'] == 0 && $selected_options[$option['option_id']] == 'unchecked') || ($variant['position'] == 1 && $selected_options[$option['option_id']] == 'checked')) {
                    $selected_options[$option['option_id']] = $variant['variant_id'];
                    if ($changed_option) {
                        $product['changed_option'] = $option['option_id'];
                    }
                }
            }
        }

        // Check, if the product has any options modifiers
        if (!empty($product['product_options'][$_id]['variants'])) {
            foreach ($product['product_options'][$_id]['variants'] as $variant) {
                if (!empty($variant['modifier']) && floatval($variant['modifier'])) {
                    $product['options_update'] = true;
                }
            }
        }
    }

    if (!empty($product['changed_option']) && empty($selected_options[$product['changed_option']]) && $product['options_type'] == 'S') {
        $product['changed_option'] = array_search($product['changed_option'], $simultaneous);
        if ($product['changed_option'] == 0) {
            unset($product['changed_option']);
            $reset = true;
            if (!empty($selected_options)) {
                foreach ($selected_options as $option_id => $variant_id) {
                    if (!isset($product['product_options'][$option_id]) || !in_array($product['product_options'][$option_id]['option_type'], array('I', 'T', 'F'))) {
                        unset($selected_options[$option_id]);
                    }
                }
            }
        }
    }

    if (empty($selected_options) && $product['options_type'] == 'P') {
        $selected_options = fn_get_default_product_options($product['product_id'], true, $product);
    }

    if (empty($product['changed_option']) && isset($reset)) {
        $product['changed_option'] = '';

    } elseif (empty($product['changed_option'])) {
        end($selected_options);
        $product['changed_option'] = key($selected_options);
    }

    if ($product['options_type'] == 'S') {
        empty($product['changed_option']) ? $allow = 1 : $allow = 0;

        foreach ($product['product_options'] as $_id => $option) {
            $product['product_options'][$_id]['disabled'] = false;

            if (in_array($option['option_type'], array('I', 'T', 'F'))) {
                continue;
            }

            $option_id = $option['option_id'];

            if ($allow >= 1) {
                unset($selected_options[$option_id]);
                $product['product_options'][$_id]['value'] = '';
            }

            if ($allow >= 2) {
                $product['product_options'][$_id]['disabled'] = true;
                continue;
            }

            if (empty($product['changed_option']) || (!empty($product['changed_option']) && $product['changed_option'] == $option_id) || $allow > 0) {
                $allow++;
            }
        }

        $product['simultaneous'] = $simultaneous;
    }

    // Restore selected values
    if (!empty($selected_options)) {
        foreach ($product['product_options'] as $_id => $option) {
            if (isset($selected_options[$option['option_id']])) {
                $product['product_options'][$_id]['value'] = $selected_options[$option['option_id']];
            }
        }
    }

    // Generate combination hash to get images. (Also, if the tracking with options, get amount and product code)
    $combination_hash = fn_generate_cart_id($product['product_id'], array('product_options' => $selected_options), true);
    $product['combination_hash'] = $combination_hash;

    // Change product code and amount
    if (!empty($product['tracking']) && $product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
        $product['hide_stock_info'] = false;
        if ($product['options_type'] == 'S') {
            foreach ($product['product_options'] as $option) {
                $option_id = $option['option_id'];
                if ($option['inventory'] == 'Y' && empty($product['selected_options'][$option_id])) {
                    $product['hide_stock_info'] = true;

                    break;
                }
            }
        }

        if (!$product['hide_stock_info']) {
            $combination = db_get_row("SELECT product_code, amount FROM ?:product_options_inventory WHERE combination_hash = ?i", $combination_hash);

            if (!empty($combination['product_code'])) {
                    $product['product_code'] = $combination['product_code'];
            }

            if (Registry::get('settings.General.inventory_tracking') == 'Y') {
                if (isset($combination['amount'])) {
                        $product['inventory_amount'] = $combination['amount'];
                } else {
                        $product['inventory_amount'] = $product['amount'] = 0;
                }
            }
        }
    }

    if (!$product['options_update']) {
        $product['options_update'] = db_get_field('SELECT COUNT(*) FROM ?:product_options_inventory WHERE product_id = ?i', $product['product_id']);
    }

    /**
     * Changes product data after applying product options rules
     *
     * @param array $product Product data
     */
    fn_set_hook('apply_options_rules_post', $product);

    return $product;
}

function fn_apply_exceptions_rules($product)
{
    /**
     * Changes product data before applying options exceptions rules
     *
     * @param array $product Product data
     */
    fn_set_hook('apply_exceptions_rules_pre', $product);

    /*	Exceptions type:
            A - Allowed
            F - Forbidden
    */
    if (empty($product['selected_options']) && $product['options_type'] == 'S') {
        return $product;
    }

    $exceptions = fn_get_product_exceptions($product['product_id'], true);

    if (empty($exceptions)) {
        return $product;
    }

    $product['options_update'] = true;
    $options = array();
    $disabled = array();

    if (Registry::get('settings.General.exception_style') == 'warning') {
        $result = fn_is_allowed_options_exceptions($exceptions, $product['selected_options'], $product['options_type'], $product['exceptions_type']);

        if (!$result) {
            $product['show_exception_warning'] = 'Y';
        }

        return $product;
    }

    foreach ($exceptions as $exception_id => $exception) {
        if ($product['options_type'] == 'S') {
            // Sequential exceptions type
            $_selected = array();

            foreach ($product['selected_options'] as $option_id => $variant_id) {
                $disable = true;
                $full = array();

                $_selected[$option_id] = $variant_id;
                $elms = array_diff($exception, $_selected);
                $_exception = $exception;

                if (!empty($elms)) {
                    foreach ($elms as $opt_id => $var_id) {
                        if ($var_id != -2 && $var_id != -1) {
                            $disable = false;
                        }
                        if ($var_id == -1) {
                            $full[$opt_id] = $var_id;
                        }
                        if (($product['exceptions_type'] == 'A' && $var_id == -1 && isset($_selected[$opt_id])) || ($product['exceptions_type'] != 'A' && $var_id == -1)) {
                            unset($elms[$opt_id]);
                            if ($product['exceptions_type'] != 'A') {
                                unset($_exception[$opt_id]);
                            }
                        }
                    }
                }

                if ($disable && !empty($elms) && count($elms) != count($full)) {
                    $vars = array_diff($elms, $full);
                    $disable = false;
                    foreach ($vars as $var) {
                        if ($var != -1) {
                            $disable = true;
                        }
                    }
                }

                if ($disable && !empty($elms) && count($elms) != count($full)) {
                    foreach ($elms as $opt_id => $var_id) {
                        $disabled[$opt_id] = true;
                    }
                } elseif ($disable && !empty($full)) {
                    foreach ($full as $opt_id => $var_id) {
                        $options[$opt_id]['any'] = true;
                    }
                } elseif (count($elms) == 1 && reset($elms) == -2) {
                    $disabled[key($elms)] = true;
                } elseif (($product['exceptions_type'] == 'A' && count($elms) + count($_selected) != count($_exception)) || ($product['exceptions_type'] == 'F' && count($elms) != 1)) {
                    continue;
                }

                if (!isset($product['simultaneous'][$option_id]) || (isset($product['simultaneous'][$option_id]) && !isset($elms[$product['simultaneous'][$option_id]]))) {
                    continue;
                }

                $elms[$product['simultaneous'][$option_id]] = ($elms[$product['simultaneous'][$option_id]] == -1) ? 'any' : $elms[$product['simultaneous'][$option_id]];
                if (isset($product['simultaneous'][$option_id]) && !empty($elms) && isset($elms[$product['simultaneous'][$option_id]])) {
                    $options[$product['simultaneous'][$option_id]][$elms[$product['simultaneous'][$option_id]]] = true;
                }
            }
        } else {
            // Parallel exceptions type
            $disable = true;
            $full = array();

            $elms = array_diff($exception, $product['selected_options']);

            if (!empty($elms)) {
                foreach ($elms as $opt_id => $var_id) {
                    if ($var_id != -2 && $var_id != -1) {
                        $disable = false;
                    }

                    if ($var_id == -1) {
                        $full[$opt_id] = $var_id;
                        unset($elms[$opt_id]);
                    }
                }
            }

            if ($disable && !empty($elms)) {
                foreach ($elms as $opt_id => $var_id) {
                    $disabled[$opt_id] = true;
                }
            } elseif ($disable && !empty($full)) {
                foreach ($full as $opt_id => $var_id) {
                    $options[$opt_id]['any'] = true;
                }
            } elseif (count($elms) == 1 && reset($elms) == -2) {
                $disabled[key($elms)] = true;
            } elseif (count($elms) == 1 && !in_array(reset($elms), $product['selected_options'])) {
                list($option_id, $variant_id) = array(key($elms), reset($elms));
                $options[$option_id][$variant_id] = true;
            }
        }
    }

    if ($product['exceptions_type'] == 'A' && $product['options_type'] == 'P') {
        foreach ($product['selected_options'] as $option_id => $variant_id) {
            $options[$option_id][$variant_id] = true;
        }
    }

    $first_elm = array();
    $clear_variants = false;

    foreach ($product['product_options'] as $_id => &$option) {
        $option_id = $option['option_id'];

        if (!in_array($option['option_type'], array('I', 'T', 'F')) && empty($first_elm)) {
            $first_elm = $product['product_options'][$_id];
        }

        if (isset($disabled[$option_id])) {
            $option['disabled'] = true;
            $option['not_required'] = true;
        }

        if (($product['options_type'] == 'S' && $option['option_id'] == $first_elm['option_id']) || (in_array($option['option_type'], array('I', 'T', 'F')))) {
            continue;
        }

        if ($product['options_type'] == 'S' && $option['disabled']) {
            if ($clear_variants) {
                $option['variants'] = array();
            }

            continue;
        }

        if (!empty($option['variants']) && $option['option_type'] != 'C') { // Exclude "C"heckboxes
            foreach ($option['variants'] as $variant_id => $variant) {
                if ($product['exceptions_type'] == 'A') {
                    // Allowed combinations
                    if (empty($options[$option_id][$variant_id]) && !isset($options[$option_id]['any'])) {
                        unset($option['variants'][$variant_id]);
                    }
                } else {
                    // Forbidden combinations
                    if (!empty($options[$option_id][$variant_id]) || isset($options[$option_id]['any'])) {
                        unset($option['variants'][$variant_id]);
                    }
                }
            }

            if (!in_array($option['value'], array_keys($option['variants']))) {
                $option['value'] = '';
            }
        }

        if (empty($option['variants'])) {
            $clear_variants = true;
        }
    }

    foreach ($product['product_options'] as $_id => &$option) {
        $option_id = $option['option_id'];
        if ($product['options_type'] == 'P' && !in_array($option['option_type'], array('I', 'T', 'F')) && empty($option['value'])) {
            if (empty($option['disabled']) && !empty($option['variants'])) {
                $variant = reset($option['variants']);
                $option['value'] = $variant['variant_id'];

                $product['selected_options'][$option_id] = $variant['variant_id'];
            }
        }
    }

    /**
     * Changes product data after applying options exceptions rules
     *
     * @param array $product    Product data
     * @param array $exceptions Options exceptions
     */
    fn_set_hook('apply_exceptions_post', $product, $exceptions);

    return $product;
}

function fn_is_allowed_options_exceptions($exceptions, $options, $o_type = 'P', $e_type = 'F')
{
    /**
     * Changes parameters before checking allowed options exceptions
     *
     * @param array  $exceptions Options exceptions
     * @param array  $options    Product options
     * @param string $o_type     Option type
     * @param string $e_type     Exception type
     */
    fn_set_hook('is_allowed_options_exceptions_pre', $exceptions, $options, $o_type, $e_type);

    $result = null;

    foreach ($options as $option_id => $variant_id) {
        if (empty($variant_id)) {
            unset($options[$option_id]);
        }
    }

    if ($e_type != 'A' || !empty($options)) {
        $in_exception = false;
        foreach ($exceptions as $exception) {
            foreach ($options as $option_id => $variant_id) {
                if (!isset($exception[$option_id])) {
                    unset($options[$option_id]);
                }
            }

            if (count($exception) != count($options)) {
                continue;
            }

            $in_exception = true;
            $diff = array_diff($exception, $options);

            if (!empty($diff)) {
                foreach ($diff as $option_id => $variant_id) {
                    if ($variant_id == -1) {
                        unset($diff[$option_id]);
                    }
                }
            }

            if (empty($diff) && $e_type == 'A') {
                $result = true;
                break;
            } elseif (empty($diff)) {
                $result = false;
                break;
            }
        }

        if (is_null($result) && $in_exception && $e_type == 'A') {
            $result = false;
        }
    }

    if (is_null($result)) {
        $result = true;
    }

    /**
     * Changes result of checking allowed options exceptions
     *
     * @param boolean $result     Result of checking options exceptions
     * @param array   $exceptions Options exceptions
     * @param array   $options    Product options
     * @param string  $o_type     Option type
     * @param string  $e_type     Exception type
     */
    fn_set_hook('is_allowed_options_exceptions_post', $result, $exceptions, $options, $o_type, $e_type);

    return $result;
}

/**
 * Checks if all selected product options are available now
 *
 * @param array $product Product data
 * @return bool true if all options are available, false otherwise
 */
function fn_is_allowed_options($product)
{
    if (empty($product['product_options'])) {
        return true;
    }

    $options = fn_get_product_options($product['product_id']);
    foreach ($product['product_options'] as $option_id => $variant_id) {
        if (!isset($options[$option_id]) || (isset($options[$option_id]['variants']) && !isset($options[$option_id]['variants'][$variant_id]))) {
            return false;
        }
    }

    return true;
}

function fn_get_product_details_views($get_default = 'default')
{
    $product_details_views = array();

    /**
     * Changes params for getting product details views or adds additional views
     *
     * @param array  $product_details_views Array for product details views templates
     * @param string $get_default           Type of default layout
     */
    fn_set_hook('get_product_details_views_pre', $product_details_views, $get_default);

    if ($get_default == 'category') {

        $parent_layout = Registry::get('settings.Appearance.default_product_details_view');
        $product_details_views['default'] = __('default_product_details_view', array(
            '[default]' => __($parent_layout)
        ));

    } elseif ($get_default != 'default') {

        $parent_layout = db_get_field("SELECT c.product_details_layout FROM ?:products_categories as pc LEFT JOIN ?:categories as c ON pc.category_id = c.category_id WHERE pc.product_id = ?i AND pc.link_type = 'M'", $get_default);
        if (empty($parent_layout) || $parent_layout == 'default') {
            $parent_layout = Registry::get('settings.Appearance.default_product_details_view');
        }

        $product_details_views['default'] = __('default_product_details_view', array(
            '[default]' => __($parent_layout)
        ));
    }

    list($theme_path, $theme_name) = fn_get_customer_layout_theme_path();

    // Get all available product_templates dirs
    $templates_path[] = $theme_path . '/templates/blocks/product_templates';

    foreach ((array) Registry::get('addons') as $addon_name => $data) {
        if ($data['status'] == 'A') {
            if (is_dir($theme_path . '/templates/addons/' . $addon_name . '/blocks/product_templates')) {
                $templates_path[] = $theme_path . '/templates/addons/' . $addon_name . '/blocks/product_templates';
            }
        }
    }

    // Scan received directories and fill the "views" array
    foreach ($templates_path as &$path) {
        $view_templates = fn_get_dir_contents($path, false, true, 'tpl');

        if (!empty($view_templates)) {
            foreach ($view_templates as &$file) {
                if ($file != '.' && $file != '..') {
                    preg_match("/(.*$theme_name\/templates\/)(.*)/", $path, $matches);

                    $_path = $matches[2]. '/' . $file;

                    // Check if the template has inner description (like a "block manager")
                    $fd = fopen($path . '/' . $file, 'r');
                    $counter = 1;
                    $_descr = '';

                    while (($s = fgets($fd, 4096)) && ($counter < 3)) {
                        preg_match('/\{\*\* template-description:(\w+) \*\*\}/i', $s, $matches);
                        if (!empty($matches[1])) {
                            $_descr = $matches[1];
                            break;
                        }
                    }

                    fclose($fd);

                    $_title = empty($_descr) ? substr($file, 0, -4) : $_descr;

                    $product_details_views[$_title] = __($_title);
                }
            }
        }
    }

    /**
     * Changes product details views
     *
     * @param array  $product_details_views Product details views
     * @param string $get_default           Type of default layout
     */
    fn_set_hook('get_product_details_views_post', $product_details_views, $get_default);

    return $product_details_views;
}

function fn_get_customer_layout_theme_path()
{
    $company_id = null;
    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        $company_id = db_get_field("SELECT MIN(company_id) FROM ?:companies");
    }

    $theme_name = fn_get_theme_path('[theme]', 'C', $company_id);
    $theme_path = fn_get_theme_path('[themes]/[theme]', 'C', $company_id);

    return array($theme_path, $theme_name);
}

function fn_get_product_details_layout($product_id)
{
    /**
     * Changes params for getting product details layout
     *
     * @param int $product_id Product identifier
     */
    fn_set_hook('get_product_details_layout_pre', $product_id);

    $selected_layout = Registry::get('settings.Appearance.default_product_details_view');

    if (!empty($product_id)) {

        $selected_layout = db_get_field("SELECT details_layout FROM ?:products WHERE product_id = ?i", $product_id);

        if (empty($selected_layout) || $selected_layout == 'default') {
            $selected_layout = db_get_field("SELECT c.product_details_layout FROM ?:products_categories as pc LEFT JOIN ?:categories as c ON pc.category_id = c.category_id WHERE pc.product_id = ?i AND pc.link_type = 'M'", $product_id);
        }

        if (empty($selected_layout) || $selected_layout == 'default') {
            $selected_layout = Registry::get('settings.Appearance.default_product_details_view');
        }
    }

    list($theme_path) = fn_get_customer_layout_theme_path();

    // Get all available product_templates dirs
    $template_path = $theme_path . '/templates/blocks/product_templates/' . $selected_layout  . '.tpl';

    $result = '';
    if (is_file($template_path)) {
        $result = 'blocks/product_templates/' . $selected_layout  . '.tpl';
    } else {
        foreach ((array) Registry::get('addons') as $addon_name => $data) {
            if ($data['status'] == 'A') {
                $template_path = $theme_path . '/templates/addons/' . $addon_name . '/blocks/product_templates/' . $selected_layout  . '.tpl';
                if (is_file($template_path)) {
                    $result = 'addons/' . $addon_name . '/blocks/product_templates/' . $selected_layout  . '.tpl';
                    break;
                }
            }
        }
    }

    if (empty($result)) {
        $result = 'blocks/product_templates/' . 'default_template.tpl';
    }

    /**
     * Changes product details layout template
     *
     * @param string $result     Product layout template
     * @param int    $product_id Product identifier
     */
    fn_set_hook('get_product_details_layout_post', $result, $product_id);

    return $result;
}

function fn_clone_product($product_id)
{
    /**
     * Adds additional actions before product cloning
     *
     * @param int $product_id Original product identifier
     */
    fn_set_hook('clone_product_pre', $product_id);

    // Clone main data
    $data = db_get_row("SELECT * FROM ?:products WHERE product_id = ?i", $product_id);
    unset($data['product_id']);
    $data['status'] = 'D';
    $data['timestamp'] = $data['updated_timestamp'] = time();
    $pid = db_query("INSERT INTO ?:products ?e", $data);

    // Clone descriptions
    $data = db_get_array("SELECT * FROM ?:product_descriptions WHERE product_id = ?i", $product_id);
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        if ($v['lang_code'] == CART_LANGUAGE) {
            $orig_name = $v['product'];
            $new_name = $v['product'].' [CLONE]';
        }

        $v['product'] .= ' [CLONE]';
        db_query("INSERT INTO ?:product_descriptions ?e", $v);
    }

    // Clone prices
    $data = db_get_array("SELECT * FROM ?:product_prices WHERE product_id = ?i", $product_id);
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        unset($v['price_id']);
        db_query("INSERT INTO ?:product_prices ?e", $v);
    }

    // Clone categories links
    $data = db_get_array("SELECT * FROM ?:products_categories WHERE product_id = ?i", $product_id);
    $_cids = array();
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        db_query("INSERT INTO ?:products_categories ?e", $v);
        $_cids[] = $v['category_id'];
    }
    fn_update_product_count($_cids);

    // Clone product options
    fn_clone_product_options($product_id, $pid);

    // Clone global linked options
    $gl_options = db_get_fields("SELECT option_id FROM ?:product_global_option_links WHERE product_id = ?i", $product_id);
    if (!empty($gl_options)) {
        foreach ($gl_options as $v) {
            db_query("INSERT INTO ?:product_global_option_links (option_id, product_id) VALUES (?i, ?i)", $v, $pid);
        }
    }

    // Clone product features
    $data = db_get_array("SELECT * FROM ?:product_features_values WHERE product_id = ?i", $product_id);
    foreach ($data as $v) {
        $v['product_id'] = $pid;
        db_query("INSERT INTO ?:product_features_values ?e", $v);
    }

    // Clone blocks
    Block::instance()->cloneDynamicObjectData('products', $product_id, $pid);

    // Clone tabs info
    ProductTabs::instance()->cloneStatuses($pid, $product_id);

    // Clone addons
    fn_set_hook('clone_product', $product_id, $pid);

    // Clone images
    fn_clone_image_pairs($pid, $product_id, 'product');

    // Clone product files
    fn_clone_product_files($product_id, $pid);

    /**
     * Adds additional actions after product cloning
     *
     * @param int    $product_id Original product identifier
     * @param int    $pid        Cloned product identifier
     * @param string $orig_name  Original product name
     * @param string $new_name   Cloned product name
     */
    fn_set_hook('clone_product_post', $product_id, $pid, $orig_name, $new_name);

    return array('product_id' => $pid, 'orig_name' => $orig_name, 'product' => $new_name);
}

/**
 * Updates product prices.
 *
 * @param int $product_id Product identifier.
 * @param array $product_data Array of product data.
 * @param int $company_id Company identifier.
 * @return array Modified <i>$product_data</i> array.
 */
function fn_update_product_prices($product_id, $product_data, $company_id = 0)
{
    $_product_data = $product_data;
    $skip_price_delete = false;
    // Update product prices
    if (isset($_product_data['price'])) {
        $_price = array (
            'price' => abs($_product_data['price']),
            'lower_limit' => 1,
        );

        if (!isset($_product_data['prices'])) {
            $_product_data['prices'][0] = $_price;
            $skip_price_delete = true;

        } else {
            unset($_product_data['prices'][0]);
            array_unshift($_product_data['prices'], $_price);
        }
    }

    if (!empty($_product_data['prices'])) {
        if (fn_allowed_for('ULTIMATE') && $company_id) {
            $table_name = '?:ult_product_prices';
            $condition = db_quote(' AND company_id = ?i', $company_id);
        } else {
            $table_name = '?:product_prices';
            $condition = '';
        }

        if (!$skip_price_delete) {
            db_query("DELETE FROM $table_name WHERE product_id = ?i $condition", $product_id);
        }

        foreach ($_product_data['prices'] as $v) {
            $v['type'] = !empty($v['type']) ? $v['type'] : 'A';
            $v['usergroup_id'] = !empty($v['usergroup_id']) ? $v['usergroup_id'] : 0;
            if ($v['lower_limit'] == 1 && $v['type'] == 'P' && $v['usergroup_id'] == 0) {
                fn_set_notification('W', __('warning'), __('cant_save_percentage_price'));
                continue;
            }
            if (!empty($v['lower_limit'])) {
                $v['product_id'] = $product_id;
                if (!empty($company_id)) {
                    $v['company_id'] = $company_id;
                }
                if ($v['type'] == 'P') {
                    $v['percentage_discount'] = ($v['price'] > 100) ? 100 : $v['price'];
                    $v['price'] = $_product_data['price'];
                }
                unset($v['type']);

                if (count($_product_data['prices']) == 1 && $skip_price_delete && empty($_product_data['create'])) {
                    $data = array(
                        'price' => $v['price']
                    );

                    db_query("UPDATE $table_name SET ?u WHERE product_id = ?i AND ((lower_limit = ?i AND usergroup_id = ?i) OR percentage_discount > ?i) ?p", $data, $v['product_id'], 1, 0, 0, $condition);
                } else {
                    db_query("REPLACE INTO $table_name ?e", $v);
                }
            }
        }
    }

    return $_product_data;
}

/**
 * Gets product prices.
 *
 * @param int $product_id Product identifier
 * @param array $product_data Array of product data. Result data will be saved in this variable.
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @param int $company_id Company identifier.
 */
function fn_get_product_prices($product_id, &$product_data, $auth, $company_id = 0)
{
    if (fn_allowed_for('ULTIMATE') && $company_id) {
        $table_name = '?:ult_product_prices';
        $condition = db_quote(' AND prices.company_id = ?i', $company_id);
    } else {
        $table_name = '?:product_prices';
        $condition = '';
    }

    // For customer
    if (AREA == 'C') {
        $_prices = db_get_hash_multi_array("SELECT prices.product_id, prices.lower_limit, usergroup_id, prices.percentage_discount, IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM $table_name prices WHERE prices.product_id = ?i $condition AND lower_limit > 1 AND prices.usergroup_id IN (?n) ORDER BY lower_limit", array('usergroup_id'), $product_id, array_merge(array(USERGROUP_ALL), $auth['usergroup_ids']));
        if (!fn_allowed_for('ULTIMATE:FREE')) {
            // If customer has usergroup and prices defined for this usergroup, get them
            if (!empty($auth['usergroup_ids'])) {
                foreach ($auth['usergroup_ids'] as $ug_id) {
                    if (!empty($_prices[$ug_id]) && sizeof($_prices[$ug_id]) > 0) {
                        if (empty($product_data['prices'])) {
                            $product_data['prices'] = $_prices[$ug_id];
                        } else {
                            foreach ($_prices[$ug_id] as $comp_data) {
                                $add_elm = true;
                                foreach ($product_data['prices'] as $price_id => $price_data) {
                                    if ($price_data['lower_limit'] == $comp_data['lower_limit']) {
                                        $add_elm = false;
                                        if ($price_data['price'] > $comp_data['price']) {
                                            $product_data['prices'][$price_id] = $comp_data;
                                        }
                                        break;
                                    }
                                }
                                if ($add_elm) {
                                    $product_data['prices'][] = $comp_data;
                                }
                            }
                        }
                    }
                }
                if (!empty($product_data['prices'])) {
                    $tmp = array();
                    foreach ($product_data['prices'] as $price_id => $price_data) {
                        $tmp[$price_id] = $price_data['lower_limit'];
                    }
                    array_multisort($tmp, SORT_ASC, $product_data['prices']);
                }
            }
        }

        // else, get prices for not members
        if (empty($product_data['prices']) && !empty($_prices[0]) && sizeof($_prices[0]) > 0) {
            $product_data['prices'] = $_prices[0];
        }
    // Other - get all
    } else {
        $product_data['prices'] = db_get_array("SELECT prices.product_id, prices.lower_limit, usergroup_id, prices.percentage_discount, IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100) as price FROM $table_name prices WHERE product_id = ?i $condition ORDER BY usergroup_id, lower_limit", $product_id);
    }
}

//
// Copy product files
//
function fn_copy_product_files($file_id, $file, $product_id, $var_prefix = 'file')
{
    /**
     * Changes params before copying product files
     *
     * @param int    $file_id    File identifier
     * @param array  $file       File data
     * @param int    $product_id Product identifier
     * @param string $var_prefix Prefix of file variables
     */
    fn_set_hook('copy_product_files_pre', $file_id, $file, $product_id, $var_prefix);

    $filename = $product_id . '/' . $file['name'];

    $_data = array();

    list($_data[$var_prefix . '_size'], $_data[$var_prefix . '_path']) = Storage::instance('downloads')->put($filename, array(
        'file' => $file['path'],
        'overwrite' => true
    ));

    $_data[$var_prefix . '_path'] = fn_basename($_data[$var_prefix . '_path']);
    db_query('UPDATE ?:product_files SET ?u WHERE file_id = ?i', $_data, $file_id);

    /**
     * Adds additional actions after product files were copied
     *
     * @param int    $file_id    File identifier
     * @param array  $file       File data
     * @param int    $product_id Product identifier
     * @param string $var_prefix Prefix of file variables
     */
    fn_set_hook('copy_product_files_post', $file_id, $file, $product_id, $var_prefix);

    return true;
}

//
// Add feature variants
//
function fn_add_feature_variant($feature_id, $variant)
{
    /**
     * Changes variant data before adding
     *
     * @param int   $feature_id Feature identifier
     * @param array $variant    Variant data
     */
    fn_set_hook('add_feature_variant_pre', $feature_id, $variant);

    if (empty($variant['variant']) && (!isset($variant['variant']) || $variant['variant'] !== '0')) {
        return false;
    }

    $variant['feature_id'] = $feature_id;
    $variant['variant_id'] = db_query("INSERT INTO ?:product_feature_variants ?e", $variant);

    foreach (fn_get_translation_languages() as $variant['lang_code'] => $_d) {
        db_query("INSERT INTO ?:product_feature_variant_descriptions ?e", $variant);
    }

    /**
     * Adds additional actions before category parent updating
     *
     * @param int   $feature_id Feature identifier
     * @param array $variant    Variant data
     */
    fn_set_hook('add_feature_variant_post', $feature_id, $variant);

    return $variant['variant_id'];
}

//
// Get products subscribers
//
function fn_get_product_subscribers($params, $items_per_page = 0)
{
    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'email' => '',
        'product_id' => 0,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    /**
     * Changes params for getting product subscribers
     *
     * @param array $params Search subscribers params
     */
    fn_set_hook('get_product_subscribers_pre', $params);

    // Init filter
    $params = LastView::instance()->update('subscribers', $params);

    $condition = '';
    $limit = '';

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND email LIKE ?l", "%" . trim($params['email']) . "%");
     }

    $sorting = db_sort($params, array('email' => 'email'), 'email', 'asc');

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_subscriptions WHERE product_id = ?i $condition", $params['product_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $subscribers = db_get_hash_array("SELECT subscription_id as subscriber_id, email FROM ?:product_subscriptions WHERE product_id = ?i $condition $sorting $limit", 'subscriber_id', $params['product_id']);

    /**
     * Changes product subscribers
     *
     * @param int   $product_id  Product identifier
     * @param array $params      Search subscribers params
     * @param array $subscribers Array of subscribers
     */
    fn_set_hook('get_product_subscribers_post', $params, $subscribers);

    return array($subscribers, $params);
}

/**
 * Gets default products sorting params
 *
 * @return array Sorting params
 */
function fn_get_default_products_sorting()
{
    $params  = explode('-', Registry::get('settings.Appearance.default_products_sorting'));
    if (is_array($params) && count($params) == 2) {
        $sorting = array (
            'sort_by' => array_shift($params),
            'sort_order' => array_shift($params),
        );
    } else {
        $default_sorting = fn_get_products_sorting();
        $sort_by = current(array_keys($default_sorting));
        $sorting = array (
            'sort_by' => $sort_by,
            'sort_order' => $default_sorting[$sort_by]['default_order'],
        );
    }

    return $sorting;
}

/**
 * Gets products from feature comparison list
 *
 * @return array List of compared products
 */
function fn_get_comparison_products()
{
    $compared_products = array();

    if (!empty($_SESSION['comparison_list'])) {
        $_products = db_get_hash_array("SELECT product_id, product FROM ?:product_descriptions WHERE product_id IN (?n) AND lang_code = ?s", 'product_id', $_SESSION['comparison_list'], CART_LANGUAGE);

        $params = array(
            'pid' => $_SESSION['comparison_list'],
        );

        list($products, $search) = fn_get_products($params);
        fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_additional' => false, 'get_options'=> false));

        $_products = array();

        foreach ($products as $product) {
            $_products[$product['product_id']] = $product;
        }
        $products = $_products;
        unset($_products);

        foreach ($_SESSION['comparison_list'] as $k => $p_id) {
            if (empty($products[$p_id])) {
                unset($_SESSION['comparison_list'][$k]);
                continue;
            }
            $compared_products[] = $products[$p_id];
        }
    }

    /**
     * Changes compared products
     *
     * @param array $compared_products List of compared products
     */
    fn_set_hook('get_comparison_products_post', $compared_products);

    return $compared_products;
}

/**
 * Physically deletes product files on disk
 *
 * @param int $file_id file ID to delete
 * @return boolean true on success, false - otherwise
 */
function fn_delete_product_files_path($file_ids)
{
    if (!empty($file_ids) && is_array($file_ids)) {
        $files_data = db_get_array("SELECT file_path, preview_path, product_id FROM ?:product_files WHERE file_id IN (?n)", $file_ids);

        foreach ($files_data as $file_data) {
            if (!empty($file_data['file_path'])) {
                Storage::instance('downloads')->delete($file_data['product_id'] . '/' . $file_data['file_path']);
            }
            if (!empty($file_data['preview_path'])) {
                Storage::instance('downloads')->delete($file_data['product_id'] . '/' . $file_data['preview_path']);
            }

            // delete empty directory
            $files = Storage::instance('downloads')->getList($file_data['product_id']);
            if (empty($files)) {
                Storage::instance('downloads')->deleteDir($file_data['product_id']);
            }

        }

        return true;
    }

    return false;
}

/**
 * Delete product files in folder
 *
 * @param int $folder_id folder ID to delete
 * @param int $product_id product ID to delete all files from it. Ignored if $folder_id is passed
 * @return boolean true on success, false - otherwise
 */
function fn_delete_product_file_folders($folder_id, $product_id = 0)
{
    if (empty($product_id) && !empty($folder_id)) {
        $product_id = db_get_field("SELECT product_id FROM ?:product_file_folders WHERE folder_id = ?i", $folder_id);
    } elseif (empty($folder_id) && empty($product_id)) {
        return false;
    }

    if (!fn_company_products_check($product_id, true)) {
        return false;
    }

    if (!empty($folder_id)) {
        $folder_ids = array($folder_id);
        $file_ids = db_get_fields("SELECT file_id FROM ?:product_files WHERE product_id = ?i AND folder_id = ?i", $product_id, $folder_id);
    } else {
        $folder_ids = db_get_fields("SELECT folder_id FROM ?:product_file_folders WHERE product_id = ?i", $product_id);
        $file_ids = db_get_fields("SELECT file_id FROM ?:product_files WHERE product_id = ?i AND folder_id IN (?n)", $product_id, $folder_ids);
    }

    if (!empty($file_ids) && fn_delete_product_files_path($file_ids) == false) {
        return false;
    }

    db_query("DELETE FROM ?:product_file_folders WHERE folder_id IN (?n)", $folder_ids);
    db_query("DELETE FROM ?:product_file_folder_descriptions WHERE folder_id IN (?n)", $folder_ids);

    db_query("DELETE FROM ?:product_files WHERE file_id IN (?n)", $file_ids);
    db_query("DELETE FROM ?:product_file_descriptions WHERE file_id IN (?n)", $file_ids);

    return true;
}

/**
 * Delete product files
 *
 * @param int $file_id file ID to delete
 * @param int $product_id product ID to delete all files from it. Ignored if $file_id is passed
 * @return boolean true on success, false - otherwise
 */
function fn_delete_product_files($file_id, $product_id = 0)
{
    if (empty($product_id) && !empty($file_id)) {
        $product_id = db_get_field("SELECT product_id FROM ?:product_files WHERE file_id = ?i", $file_id);
    } elseif (empty($folder_id) && empty($product_id)) {
        return false;
    }

    if (!fn_company_products_check($product_id, true)) {
        return false;
    }

    if (!empty($file_id)) {
        $file_ids = array($file_id);
    } else {
        $file_ids = db_get_fields("SELECT file_id FROM ?:product_files WHERE product_id = ?i", $product_id);
    }

    if (fn_delete_product_files_path($file_ids) == false) {
        return false;
    }

    db_query("DELETE FROM ?:product_files WHERE file_id IN (?n)", $file_ids);
    db_query("DELETE FROM ?:product_file_descriptions WHERE file_id IN (?n)", $file_ids);

    return true;
}

/**
 * Update product folder
 *
 * @param array $product_file_fodler folder data
 * @param int $folder_id folder ID for update, if empty - new folder will be created
 * @param string $lang_code language code to update folder description
 * @return int folder ID
 */

function fn_update_product_file_folder($product_file_folder, $folder_id, $lang_code = DESCR_SL)
{
    if (!fn_company_products_check($product_file_folder['product_id'], true)) {
        return false;
    }

    if (empty($folder_id)) {

        $product_file_folder['folder_id'] = $folder_id = db_query('INSERT INTO ?:product_file_folders ?e', $product_file_folder);

        foreach (fn_get_translation_languages() as $product_file_folder['lang_code'] => $v) {
            db_query('INSERT INTO ?:product_file_folder_descriptions ?e', $product_file_folder);
        }

    } else {
        db_query('UPDATE ?:product_file_folders SET ?u WHERE folder_id = ?i', $product_file_folder, $folder_id);
        db_query('UPDATE ?:product_file_folder_descriptions SET ?u WHERE folder_id = ?i AND lang_code = ?s', $product_file_folder, $folder_id, $lang_code);
    }

    return $folder_id;
}

/**
 * Update product file
 *
 * @param array $product_file file data
 * @param int $file_id file ID for update, if empty - new file will be created
 * @param string $lang_code language code to update file description
 * @return boolean true on success, false - otherwise
 */
function fn_update_product_file($product_file, $file_id, $lang_code = DESCR_SL)
{
    if (!fn_company_products_check($product_file['product_id'], true)) {
        return false;
    }

    $uploaded_data = fn_filter_uploaded_data('base_file');
    $uploaded_preview_data = fn_filter_uploaded_data('file_preview');

    if (!empty($file_id) || !empty($uploaded_data[$file_id])) {

        db_query("UPDATE ?:products SET is_edp = 'Y' WHERE product_id = ?i", $product_file['product_id']);

        if (!empty($uploaded_data[$file_id])) {
            $product_file['file_name'] = empty($product_file['file_name']) ? $uploaded_data[$file_id]['name'] : $product_file['file_name'];
        }

        // Remove old file before uploading a new one
        if (!empty($file_id)) {
            $dir = $product_file['product_id'];
            $old_file = db_get_row('SELECT file_path, preview_path FROM ?:product_files WHERE product_id = ?i AND file_id = ?i', $product_file['product_id'], $file_id);

            if (!empty($uploaded_data) && !empty($old_file['file_path'])) {
                Storage::instance('downloads')->delete($dir . '/' . $old_file['file_path']);
            }

            if (!empty($uploaded_preview_data) && !empty($old_file['preview_path'])) {
                Storage::instance('downloads')->delete($dir . '/' . $old_file['preview_path']);
            }
        }

        // Update file data
        if (empty($file_id)) {
            $product_file['file_id'] = $file_id = db_query('INSERT INTO ?:product_files ?e', $product_file);

            foreach (fn_get_translation_languages() as $product_file['lang_code'] => $v) {
                db_query('INSERT INTO ?:product_file_descriptions ?e', $product_file);
            }

            $uploaded_id = 0;
        } else {

            db_query('UPDATE ?:product_files SET ?u WHERE file_id = ?i', $product_file, $file_id);
            db_query('UPDATE ?:product_file_descriptions SET ?u WHERE file_id = ?i AND lang_code = ?s', $product_file, $file_id, $lang_code);

            $uploaded_id = $file_id;
        }

        // Copy base file
        if (!empty($uploaded_data[$uploaded_id])) {
            fn_copy_product_files($file_id, $uploaded_data[$uploaded_id], $product_file['product_id']);
        }

        // Copy preview file
        if (!empty($uploaded_preview_data[$uploaded_id])) {
            fn_copy_product_files($file_id, $uploaded_preview_data[$uploaded_id], $product_file['product_id'], 'preview');
        }
    }

    return $file_id;
}

/**
 * Clone product folders
 *
 * @param int $source_id source product ID
 * @param int $target_id target product ID
 * @return associative array with the old folder IDs as keys and the new folder IDs as values
 */
function fn_clone_product_file_folders($source_id, $target_id)
{
    $data = db_get_array("SELECT * FROM ?:product_file_folders WHERE product_id = ?i", $source_id);
    $new_folder_ids = array();
    if (!empty($data)) {
        foreach ($data as $v) {
            $folder_descr = db_get_array("SELECT * FROM ?:product_file_folder_descriptions WHERE folder_id = ?i", $v['folder_id']);

            $v['product_id'] = $target_id;
            $old_folder_id = $v['folder_id'];
            unset($v['folder_id']);

            $new_folder_ids[$old_folder_id] = $new_folder_id = db_query("INSERT INTO ?:product_file_folders ?e", $v);

            foreach ($folder_descr as $key => $descr) {
                $descr['folder_id'] = $new_folder_id;
                db_query("INSERT INTO ?:product_file_folder_descriptions ?e", $descr);
            }
        }
    }

    return $new_folder_ids;
}

/**
 * Clone product files
 *
 * @param int $source_id source product ID
 * @param int $target_id target product ID
 * @return boolean true on success, false - otherwise
 */
function fn_clone_product_files($source_id, $target_id)
{
    $data = db_get_array("SELECT * FROM ?:product_files WHERE product_id = ?i", $source_id);

    $new_folder_ids = fn_clone_product_file_folders($source_id, $target_id);

    if (!empty($data)) {
        foreach ($data as $v) {
            $file_descr = db_get_array("SELECT * FROM ?:product_file_descriptions WHERE file_id = ?i", $v['file_id']);

            $v['product_id'] = $target_id;
            unset($v['file_id']);

            // set new folder id
            if (!empty($v['folder_id'])) {
                $v['folder_id'] = $new_folder_ids[$v['folder_id']];
            }

            $new_file_id = db_query("INSERT INTO ?:product_files ?e", $v);

            foreach ($file_descr as $key => $descr) {
                $descr['file_id'] = $new_file_id;
                db_query("INSERT INTO ?:product_file_descriptions ?e", $descr);
            }

        }

        Storage::instance('downloads')->copy($source_id, $target_id);

        return true;
    }

    return false;
}

/**
 * Download product file
 *
 * @param int $file_id file ID
 * @param boolean $is_preview flag indicates that we download file itself or just preview
 * @param string $ekey temporary key to download file from customer area
 * @param string $area current working area
 * @return file starts to download on success, boolean false in case of fail
 */
function fn_get_product_file($file_id, $is_preview = false, $ekey = '', $area = AREA)
{
    if (!empty($file_id)) {
        $column = $is_preview ? 'preview_path' : 'file_path';
        $file_data = db_get_row("SELECT $column, product_id FROM ?:product_files WHERE file_id = ?i", $file_id);

        if (fn_allowed_for('MULTIVENDOR') && $area == 'A' && !fn_company_products_check($file_data['product_id'], true)) {
            return false;
        }

        if (!empty($ekey)) {

            $ekey_info = fn_get_product_edp_info($file_data['product_id'], $ekey);

            if (empty($ekey_info) || $ekey_info['file_id'] != $file_id) {
                return false;
            }

            // Increase downloads for this file
            $max_downloads = db_get_field("SELECT max_downloads FROM ?:product_files WHERE file_id = ?i", $file_id);
            $file_downloads = db_get_field("SELECT downloads FROM ?:product_file_ekeys WHERE ekey = ?s AND file_id = ?i", $ekey, $file_id);

            if (!empty($max_downloads)) {
                if ($file_downloads >= $max_downloads) {
                    return false;
                }
            }

            db_query('UPDATE ?:product_file_ekeys SET ?u WHERE file_id = ?i AND product_id = ?i AND order_id = ?i', array('downloads' => $file_downloads + 1), $file_id, $file_data['product_id'], $ekey_info['order_id']);
        }

        Storage::instance('downloads')->get($file_data['product_id'] . '/' . $file_data[$column]);
    }

    return false;
}

/**
 * Prepares product quick view data
 *
 * @param array $params Parameteres for gathering additional quick view data
 * @return boolean Always true
 */
 function fn_prepare_product_quick_view($params)
 {
    if (!empty($params['prev_url'])) {
        Registry::get('view')->assign('redirect_url', $params['prev_url']);
    }

    /**
     * Additional actions for product quick view
     *
     * @param array $_REQUEST Request parameters
     */
    fn_set_hook('prepare_product_quick_view', $_REQUEST);

    return true;
}

function fn_get_product_pagination_steps($cols, $products_per_page)
{
    $min_range = $cols * 4;
    $max_ranges = 4;
    $steps = array();

    for ($i = 0; $i < $max_ranges; $i++) {
        $steps[] = $min_range;
        $min_range = $min_range * 2;
    }

    $steps[] = (int) $products_per_page;

    $steps = array_unique($steps);

    sort($steps, SORT_NUMERIC);

    return $steps;
}

function fn_get_product_option_data($option_id, $product_id, $lang_code = DESCR_SL)
{
    $extra_variant_fields = '';

    $fields = "a.*, b.option_name, b.option_text, b.description, b.inner_hint, b.incorrect_message, b.comment";
    $join = db_quote(" LEFT JOIN ?:product_options_descriptions as b ON a.option_id = b.option_id AND b.lang_code = ?s"
        . " LEFT JOIN ?:product_global_option_links as c ON c.option_id = a.option_id", $lang_code);
    $condition = db_quote("a.option_id = ?i AND a.product_id = ?i", $option_id, $product_id);

    /**
     * Changes params before option data selecting
     *
     * @param int    $option_id            Option identifier
     * @param int    $product_id           Product identifier
     * @param string $fields               Fields to be selected
     * @param string $condition            String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
     * @param string $join                 String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
     * @param string $extra_variant_fields Additional variant fields to be selected
     * @param string $lang_code            2-letters language code
     */
    fn_set_hook('get_product_option_data_pre', $option_id, $product_id, $fields, $condition, $join, $extra_variant_fields, $lang_code);

    $opt = db_get_row(
        "SELECT " . $fields
        . " FROM ?:product_options as a" . $join
        . " WHERE " . $condition
        . " ORDER BY a.position"
    );

    if (!empty($opt)) {
        $_cond = ($opt['option_type'] == 'C') ? ' AND a.position = 1' : '';

        $join = '';
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $extra_variant_fields .= 'IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier, a.modifier) as modifier, ';
            $extra_variant_fields .= 'IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier_type, a.modifier_type) as modifier_type, ';
            $join .= db_quote(' LEFT JOIN ?:ult_product_option_variants shared_option_variants ON shared_option_variants.variant_id = a.variant_id AND shared_option_variants.company_id = ?i', Registry::get('runtime.company_id'));
        }

        $opt['variants'] = db_get_hash_array("SELECT a.variant_id, a.position, a.modifier, a.modifier_type, a.weight_modifier, a.weight_modifier_type, a.status, $extra_variant_fields b.variant_name FROM ?:product_option_variants as a LEFT JOIN ?:product_option_variants_descriptions as b ON a.variant_id = b.variant_id AND b.lang_code = ?s $join WHERE a.option_id = ?i $_cond ORDER BY a.position", 'variant_id', $lang_code, $option_id);

        if (!empty($opt['variants'])) {
            foreach ($opt['variants'] as $k => $v) {
                $opt['variants'][$k]['image_pair'] = fn_get_image_pairs($v['variant_id'], 'variant_image', 'V', true, true, $lang_code);
            }
        }
    }

    /**
     * Changes option data
     *
     * @param array  $opt        Option data
     * @param int    $product_id Product identifier
     * @param string $lang_code  2-letters language code
     */
    fn_set_hook('get_product_option_data_post', $opt, $product_id, $lang_code);

    return $opt;
}

/**
 * Product fields for multi update
 *
 * @return array Product fields
 */
function fn_get_product_fields()
{
    $fields = array(
        array(
            'name' => '[data][popularity]',
            'text' => __('popularity')
        ),
        array(
            'name' => '[data][status]',
            'text' => __('status'),
            'disabled' => 'Y'
        ),
        array(
            'name' => '[data][product]',
            'text' => __('product_name'),
            'disabled' => 'Y'
        ),
        array(
            'name' => '[data][price]',
            'text' => __('price')
        ),
        array(
            'name' => '[data][list_price]',
            'text' => __('list_price')
        ),
        array(
            'name' => '[data][short_description]',
            'text' => __('short_description')
        ),
        array(
            'name' => '[categories]',
            'text' => __('categories')
        ),
        array(
            'name' => '[data][full_description]',
            'text' => __('full_description')
        ),
        array(
            'name' => '[data][search_words]',
            'text' => __('search_words')
        ),
        array(
            'name' => '[data][meta_keywords]',
            'text' => __('meta_keywords')
        ),
        array(
            'name' => '[data][meta_description]',
            'text' => __('meta_description')
        ),
        array(
            'name' => '[main_pair]',
            'text' => __('image_pair')
        ),
        array(
            'name' => '[data][min_qty]',
            'text' => __('min_order_qty')
        ),
        array(
            'name' => '[data][max_qty]',
            'text' => __('max_order_qty')
        ),
        array(
            'name' => '[data][qty_step]',
            'text' => __('quantity_step')
        ),
        array(
            'name' => '[data][list_qty_count]',
            'text' => __('list_quantity_count')
        ),
        array(
            'name' => '[data][product_code]',
            'text' => __('sku')
        ),
        array(
            'name' => '[data][weight]',
            'text' => __('weight')
        ),
        array(
            'name' => '[data][shipping_freight]',
            'text' => __('shipping_freight')
        ),
        array(
            'name' => '[data][is_edp]',
            'text' => __('downloadable')
        ),
        array(
            'name' => '[data][edp_shipping]',
            'text' => __('edp_enable_shipping')
        ),
        array(
            'name' => '[data][free_shipping]',
            'text' => __('free_shipping')
        ),
        array(
            'name' => '[data][feature_comparison]',
            'text' => __('feature_comparison')
        ),
        array(
            'name' => '[data][zero_price_action]',
            'text' => __('zero_price_action')
        ),
        array(
            'name' => '[data][taxes]',
            'text' => __('taxes')
        ),
        array(
            'name' => '[data][features]',
            'text' => __('features')
        ),
        array(
            'name' => '[data][page_title]',
            'text' => __('page_title')
        ),
        array(
            'name' => '[data][timestamp]',
            'text' => __('creation_date')
        ),
        array(
            'name' => '[data][amount]',
            'text' => __('quantity')
        ),
        array(
            'name' => '[data][avail_since]',
            'text' => __('available_since')
        ),
        array(
            'name' => '[data][out_of_stock_actions]',
            'text' => __('out_of_stock_actions')
        ),
        array(
            'name' => '[data][details_layout]',
            'text' => __('product_details_layout')
        ),
        array(
            'name' => '[data][min_items_in_box]',
            'text' => __('minimum_items_in_box')
        ),
        array(
            'name' => '[data][max_items_in_box]',
            'text' => __('maximum_items_in_box')
        ),
        array(
            'name' => '[data][box_length]',
            'text' => __('box_length')
        ),
        array(
            'name' => '[data][box_width]',
            'text' => __('box_width')
        ),
        array(
            'name' => '[data][box_height]',
            'text' => __('box_height')
        ),
    );

    if (!fn_allowed_for('ULTIMATE:FREE') && Registry::get('config.tweaks.disable_localizations') == false) {
        $fields[] =  array(
            'name' => '[data][localization]',
            'text' => __('localization')
        );

        $fields[] =  array(
            'name' => '[data][usergroup_ids]',
            'text' => __('usergroups')
        );
    }

    if (Registry::get('settings.General.inventory_tracking') == "Y") {
        $fields[] = array(
            'name' => '[data][tracking]',
            'text' => __('inventory')
        );
    }

    if (fn_allowed_for('ULTIMATE,MULTIVENDOR') && !Registry::get('runtime.company_id')) {
        $fields[] = array(
            'name' => '[data][company_id]',
            'text' => fn_allowed_for('MULTIVENDOR') ? __('vendor') : __('store')
        );
    }

    /**
     * Hook for change fields array
     *
     * @param array $fields Product fields
     */
    fn_set_hook('get_product_fields', $fields);

    return $fields;
}

/**
 * Get product code by product_id
 *
 * @param int $product_id
 * @param array $product_options
 * @return (string) product code
 */
function fn_get_product_code($product_id, $product_options = array())
{
    if (!empty($product_id)) {
        $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id);
        $data['extra']['product_options'] = (array) $product_options;
        $combination_hash = fn_generate_cart_id($product_id, $data['extra']);
        $product_code = db_get_field("SELECT product_code FROM ?:product_options_inventory WHERE combination_hash = ?i AND product_id = ?i", $combination_hash, $product_id);

        if (empty($product_code) || $tracking != ProductTracking::TRACK_WITH_OPTIONS) {
            $product_code = db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $product_id);
        }

        return $product_code;
    }

    return '';
}

function fn_get_product_counts_by_category($params, $lang_code = CART_LANGUAGE)
{
    $default_params = array(
        'company_id' => 0,
        'sort_by' => 'position',
        'sort_order' => 'asc',
    );

    $params = array_merge($default_params, $params);

    $sort_fields = array(
        'position' => '?:categories.position',
        'category' => '?:category_descriptions.category',
        'count' => 'count',
    );

    $sort = db_sort($params, $sort_fields, $default_params['sort_by'], $default_params['sort_order']);

    $condition = $join = '';
    if (!empty($params['company_id'])) {
        if (is_array($params['company_id'])) {
            $condition .= db_quote(" AND ?:products.company_id IN (?a) ", $params['company_id']);
        } else {
            $condition .= db_quote(" AND ?:products.company_id = ?i ", $params['company_id']);
        }
    }
    $condition .= db_quote(" AND ?:category_descriptions.lang_code = ?s ", $lang_code);

    $join .= 'JOIN ?:products ON ?:products_categories.product_id = ?:products.product_id ';
    $join .= 'JOIN ?:categories ON ?:products_categories.category_id = ?:categories.category_id ';
    $join .= 'JOIN ?:category_descriptions ON ?:products_categories.category_id = ?:category_descriptions.category_id ';

    $result = db_get_array("SELECT COUNT(*) as count, ?:category_descriptions.category, ?:category_descriptions.category_id FROM ?:products_categories ?p WHERE 1 ?p GROUP BY ?:products_categories.category_id ?p", $join, $condition, $sort);

    return $result;
}

/**
 * Gets categefories and products totals data
 *
 * @return array Array with categories and products totals
 */
function fn_get_categories_stats()
{
    $stats = array();
    $params = array(
        'only_short_fields' => true,
        'extend' => array('companies', 'sharing'),
        'get_conditions' => true,
    );

    list($fields, $join, $condition) = fn_get_products($params);

    db_query('SELECT SQL_CALC_FOUND_ROWS 1 FROM ?:products AS products' . $join . ' WHERE 1 ' . $condition . 'GROUP BY products.product_id');
    $stats['products_total'] = db_get_found_rows();

    $params = array(
        'get_conditions' => true
    );
    list($fields, $join, $condition, $group_by, $sorting, $limit) = fn_get_categories($params);
    $stats['categories_total'] = db_get_field('SELECT COUNT(*) FROM ?:categories WHERE 1 ?p', $condition);

    $params = array(
        'get_conditions' => true,
        'status' => 'A'
    );
    list($fields, $join, $condition, $group_by, $sorting, $limit) = fn_get_categories($params);
    $stats['categories_active'] = db_get_field('SELECT COUNT(*) FROM ?:categories WHERE 1 ?p', $condition);

    $params = array(
        'get_conditions' => true,
        'status' => 'H'
    );
    list($fields, $join, $condition, $group_by, $sorting, $limit) = fn_get_categories($params);
    $stats['categories_hidden'] = db_get_field('SELECT COUNT(*) FROM ?:categories WHERE 1 ?p', $condition);

    $params = array(
        'get_conditions' => true,
        'status' => 'D'
    );
    list($fields, $join, $condition, $group_by, $sorting, $limit) = fn_get_categories($params);
    $stats['categories_disabled'] = db_get_field('SELECT COUNT(*) FROM ?:categories WHERE 1 ?p', $condition);

    return $stats;
}

/**
 * Gets all available brands.
 *
 * @param array $object Block manager object
 * @param array $block Current block settings
 * @param array $scheme Scheme of current block
 * @return array Found brands
 */
function fn_get_all_brands($object, $block, $scheme)
{
    $params = array(
        'exclude_group' => true,
        'get_descriptions' => true,
        'feature_types' => array('E'),
        'variants' => true,
        'plain' => trues,
    );

    list($features) = fn_get_product_features($params, 0);

    $variants = array();

    foreach ($features as $feature) {
        $variants = array_merge($variants, $feature['variants']);
    }

    return $variants;
}

function fn_update_product_categories($product_id, $product_data)
{
    $existing_categories = db_get_hash_array("SELECT category_id, link_type, position FROM ?:products_categories WHERE product_id = ?i", 'category_id', $product_id);

    $rebuild = false;
    if (!empty($product_data['category_ids'])) {
        if (empty($product_data['main_category'])) {
            $product_data['main_category'] = reset($product_data['category_ids']);
        }

        if (sizeof($product_data['category_ids']) == sizeof($existing_categories)) {
            if (isset($existing_categories[$product_data['main_category']]) && $existing_categories[$product_data['main_category']]['link_type'] != 'M') {
                $rebuild = true;
            }

            foreach ($product_data['category_ids'] as $cid) {
                if (!isset($existing_categories[$cid])) {
                    $rebuild = true;
                }
            }
        } else {
            $rebuild = true;
        }
    }

    if ($rebuild == true) {

        db_query("DELETE FROM ?:products_categories WHERE product_id = ?i", $product_id);
        foreach ($product_data['category_ids'] as $cid) {
            $_data = array(
                'product_id' => $product_id,
                'category_id' => $cid,
                'position' => isset($existing_categories[$cid]) ? $existing_categories[$cid]['position'] : 0,
                'link_type' => $product_data['main_category'] == $cid ? 'M' : 'A'
            );
            db_query("INSERT INTO ?:products_categories ?e", $_data);
        }

        fn_update_product_count(fn_array_merge($product_data['category_ids'], array_keys($existing_categories), false));
    }
}

/**
 * Checks if product linked to any category from the owner company
 *
 * @param int $product_id Product ID
 * @param array $category_ids List of category ids
 * @return bool True if linked
 */
function fn_check_owner_categories($company_id, $category_ids)
{
    $linked_to_categories =  db_get_field('SELECT COUNT(*) FROM ?:categories WHERE company_id = ?i AND category_id IN (?a)', $company_id, $category_ids);

    return !empty($linked_to_categories);
}
