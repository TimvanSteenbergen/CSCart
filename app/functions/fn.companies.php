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
use Tygh\Menu;
use Tygh\Settings;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\ProductTabs;
use Tygh\BlockManager\Block;
use Tygh\Navigation\LastView;
use Tygh\Tools\Url;

/**
 * Gets brief company data array: <i>(company_id => company_name)</i>
 *
 * @param array $params Array of search params:
 * <ul>
 *        <li>string status - Status field from the <i>?:companies table</i></li>
 *        <li>string item_ids - Comma separated list of company IDs</li>
 *        <li>int displayed_vendors - Number of companies for displaying. Will be used as LIMIT condition</i>
 * </ul>
 * Global variable <i>$_REQUEST</i> can be passed as argument
 * @return mixed If <i>$params</i> was not empty returns array:
 * <ul>
 *   <li>companies - Hash array of companies <i>(company_id => company)</i></li>
 *   <li>count - Number of returned companies</li>
 * </ul>
 * else returns hash array of companies <i>(company_id => company)</i></li>
 */
function fn_get_short_companies($params = array())
{
    $condition = $limit = $join = $companies = '';

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:companies.status = ?s ", $params['status']);
    }

    if (!empty($params['item_ids'])) {
        $params['item_ids'] = fn_explode(",", $params['item_ids']);
        $condition .= db_quote(" AND ?:companies.company_id IN (?n) ", $params['item_ids']);
    }

    if (!empty($params['displayed_vendors'])) {
        $limit = 'LIMIT ' . $params['displayed_vendors'];
    }

    $condition .= Registry::get('runtime.company_id') ? fn_get_company_condition('?:companies.company_id', true, Registry::get('runtime.company_id')) : '';

    fn_set_hook('get_short_companies', $params, $condition, $join, $limit);

    $count = db_get_field("SELECT COUNT(*) FROM ?:companies $join WHERE 1 $condition");

    $_companies = db_get_hash_single_array("SELECT ?:companies.company_id, ?:companies.company FROM ?:companies $join WHERE 1 $condition ORDER BY ?:companies.company $limit", array('company_id', 'company'));

    if (!fn_allowed_for('ULTIMATE')) {
        $companies[0] = Registry::get('settings.Company.company_name');
        $companies = $companies + $_companies;
    } else {
        $companies = $_companies;
    }

    $return = array(
        'companies' => $companies,
        'count' => $count,
    );

    if (!empty($params)) {
        unset($return['companies'][0]);

        return array($return);
    }

    return $companies;
}

/**
 * Gets company name by id.
 *
 * @staticvar array $cache_names Static cache for company names
 * @param int $company_id Company id
 * @param string $zero_company_name_lang_var If <i>$company_id</i> is empty, this name will be returned (used in MVE for pages and shippings)
 * @return mixed Company name string in case company name for the given id is found, <i>null</i> otherwise
 */
function fn_get_company_name($company_id, $zero_company_name_lang_var = '')
{
    static $cache_names = array();

    if (empty($company_id)) {
        return __($zero_company_name_lang_var);
    }

    if (!isset($cache_names[$company_id])) {
        if (Registry::get('runtime.company_id') === $company_id) {
            $cache_names[$company_id] = Registry::get('runtime.company_data.company');
        } else {
            $cache_names[$company_id] = db_get_field("SELECT company FROM ?:companies WHERE company_id = ?i", $company_id);
        }
    }

    return $cache_names[$company_id];
}

/**
 * Gets company data array
 *
 * @param array $params Array of search params:
 * <ul>
 *		  <li>string company - Name of company</li>
 *		  <li>string status - Status of company</li>
 *		  <li>string email - Email of company</li>
 *		  <li>string address - Address of company</li>
 *		  <li>string zipcode - Zipcode of company</li>
 *		  <li>string country - 2-letters country code of company country</li>
 *		  <li>string state - State code of company</li>
 *		  <li>string city - City of company</li>
 *		  <li>string phone - Phone of company</li>
 *		  <li>string url - URL address of company</li>
 *		  <li>string fax - Fax number of company</li>
 *		  <li>mixed company_id - Company ID, array with company IDs or comma-separated list of company IDs.
 * If defined, data will be returned only for companies with such company IDs.</li>
 *		  <li>int exclude_company_id - Company ID, if defined,
 * result array will not include the data for company with such company ID.</li>
 *		  <li>int page - First page to displaying list of companies (if <i>$items_per_page</i> it not empty.</li>
 *		  <li>string sort_order - <i>ASC</i> or <i>DESC</i>: database query sorting order</li>
 *		  <li>string sort_by - One or list of database fields for sorting.</li>
 * </ul>
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @param int $items_per_page
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array Array:
 * <ul>
 *		<li>0 - First element is array with companies data.</li>
 *		<li>1 - is possibly modified array with searh params (<i>$params</i>).</li>
 * </ul>
 */
function fn_get_companies($params, &$auth, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    // Init filter
    $_view = 'companies';
    $params = LastView::instance()->update($_view, $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:companies.company_id',
        '?:companies.lang_code',
        '?:companies.email',
        '?:companies.company',
        '?:companies.timestamp',
        '?:companies.status',
    );

    if (fn_allowed_for('ULTIMATE')) {
        $fields[] = '?:companies.storefront';
        $fields[] = '?:companies.secure_storefront';
    }

    // Define sort fields
    $sortings = array (
        'id' => '?:companies.company_id',
        'company' => '?:companies.company',
        'email' => '?:companies.email',
        'date' => '?:companies.timestamp',
        'status' => '?:companies.status',
    );

    if (fn_allowed_for('ULTIMATE')) {
        $sortings['storefront'] = '?:companies.storefront';
    }

    $condition = $join = $group = '';

    $condition .= fn_get_company_condition('?:companies.company_id');

    $group .= " GROUP BY ?:companies.company_id";

    if (isset($params['company']) && fn_string_not_empty($params['company'])) {
        $condition .= db_quote(" AND ?:companies.company LIKE ?l", "%".trim($params['company'])."%");
    }

    if (!empty($params['status'])) {
        if (is_array($params['status'])) {
            $condition .= db_quote(" AND ?:companies.status IN (?a)", $params['status']);
        } else {
            $condition .= db_quote(" AND ?:companies.status = ?s", $params['status']);
        }
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND ?:companies.email LIKE ?l", "%".trim($params['email'])."%");
    }

    if (isset($params['address']) && fn_string_not_empty($params['address'])) {
        $condition .= db_quote(" AND ?:companies.address LIKE ?l", "%".trim($params['address'])."%");
    }

    if (isset($params['zipcode']) && fn_string_not_empty($params['zipcode'])) {
        $condition .= db_quote(" AND ?:companies.zipcode LIKE ?l", "%".trim($params['zipcode'])."%");
    }

    if (!empty($params['country'])) {
        $condition .= db_quote(" AND ?:companies.country = ?s", $params['country']);
    }

    if (isset($params['state']) && fn_string_not_empty($params['state'])) {
        $condition .= db_quote(" AND ?:companies.state LIKE ?l", "%".trim($params['state'])."%");
    }

    if (isset($params['city']) && fn_string_not_empty($params['city'])) {
        $condition .= db_quote(" AND ?:companies.city LIKE ?l", "%".trim($params['city'])."%");
    }

    if (isset($params['phone']) && fn_string_not_empty($params['phone'])) {
        $condition .= db_quote(" AND ?:companies.phone LIKE ?l", "%".trim($params['phone'])."%");
    }

    if (isset($params['url']) && fn_string_not_empty($params['url'])) {
        $condition .= db_quote(" AND ?:companies.url LIKE ?l", "%".trim($params['url'])."%");
    }

    if (isset($params['fax']) && fn_string_not_empty($params['fax'])) {
        $condition .= db_quote(" AND ?:companies.fax LIKE ?l", "%".trim($params['fax'])."%");
    }

    if (!empty($params['company_id'])) {
        $condition .= db_quote(' AND ?:companies.company_id IN (?n)', $params['company_id']);
    }

    if (!empty($params['exclude_company_id'])) {
        $condition .= db_quote(' AND ?:companies.company_id != ?i', $params['exclude_company_id']);
    }

    fn_set_hook('get_companies', $params, $fields, $sortings, $condition, $join, $auth, $lang_code, $group);

    $sorting = db_sort($params, $sortings, 'company', 'asc');

    // Paginate search results
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:companies.company_id)) FROM ?:companies $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $companies = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:companies $join WHERE 1 $condition $group $sorting $limit");

    return array($companies, $params);
}

function fn_company_products_check($product_ids, $notify = false)
{
    if (!empty($product_ids)) {
        $c = db_get_field("SELECT count(*) FROM ?:products WHERE product_id IN (?n) ?p", $product_ids, fn_get_company_condition('?:products.company_id'));
        if (count((array) $product_ids) == $c) {
            return true;
        } else {
            if ($notify) {
                fn_company_access_denied_notification();
            }

            return false;
        }
    }

    return true;
}

function fn_company_access_denied_notification()
{
    fn_set_notification('W', __('warning'), __('access_denied'), '', 'company_access_denied');
}

/**
 * Gets part of SQL-query with codition for company_id field.
 *
 * @staticvar array $sharing_schema Local static cache for sharing schema
 * @param string $db_field Field name (usually table_name.company_id)
 * @param bool $add_and Include or not AND keyword berofe condition.
 * @param mixed $company_id Company ID for using in SQL condition.
 * @param bool $show_admin Include or not company_id == 0 in condition (used in the MultiVendor Edition)
 * @param bool $force_condition_for_area_c Used in the MultiVendor Edition. By default, SQL codition should be empty in the customer area. But in some cases,
 * this condition should be enabled in the customer area. If <i>$force_condition_for_area_c</i> is set, condtion will be formed for the customer area.
 * @return string Part of SQL query with company ID condition
 */
function fn_get_company_condition($db_field = 'company_id', $add_and = true, $company_id = '', $show_admin = false, $force_condition_for_area_c = false)
{
    if (fn_allowed_for('ULTIMATE')) {
        // Completely remove company condition for sharing objects

        static $sharing_schema;

        if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
            $sharing_schema = fn_get_schema('sharing', 'schema');
        }

        // Check if table was passed
        if (strpos($db_field, '.')) {
            list($table, $field) = explode('.', $db_field);
            $table = str_replace('?:', '', $table);

            // Check if the db_field table is in the schema
            if (isset($sharing_schema[$table])) {
                return '';
            }

        } else {
            return '';
        }

        if (Registry::get('runtime.company_id') && !$company_id) {
            $company_id = Registry::get('runtime.company_id');
        }
    }

    if ($company_id === '') {
        $company_id = Registry::ifGet('runtime.company_id', '');
    }

    $skip_cond = (AREA == 'C' && !$force_condition_for_area_c && !fn_allowed_for('ULTIMATE'));

    if (!$company_id || $skip_cond) {
        $cond = '';
    } else {
        $cond = $add_and ? ' AND' : '';
        // FIXME 2tl show admin
        if ($show_admin && $company_id) {
            $cond .= " $db_field IN (0, $company_id)";
        } else {
            $cond .= " $db_field = $company_id";
        }
    }

    return $cond;
}

/**
 * Gets company data by it ID
 *
 * @staticvar array $company_data_cache Array with cached companies data
 * @param int $company_id Company ID
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @param array $extra Array with extra parameters
 * @return boolean|array with company data
 */
function fn_get_company_data($company_id, $lang_code = DESCR_SL, $extra = array())
{
    static $company_data_cache = array();

    if (empty($company_id)) {
        return false;
    }

    $cache_key = md5($company_id . $lang_code . serialize($extra));

    if (empty($extra['skip_cache']) && isset($company_data_cache[$cache_key])) {
        return $company_data_cache[$cache_key];
    }

    /**
     * Hook for changing incoming parameters
     *
     * @param int    $company_id Company ID
     * @param string $lang_code  2-letter language code (e.g. 'en', 'ru', etc.)
     * @param array  $extra      Array with extra parameters
     */
    fn_set_hook('get_company_data_pre', $company_id, $lang_code, $extra);

    $fields = array (
        'companies.*',
    );

    if (fn_allowed_for('MULTIVENDOR')) {
        $fields[] = 'company_descriptions.company_description';
    }

    $join = '';

    if (fn_allowed_for('MULTIVENDOR')) {
        $join .= db_quote(
            ' LEFT JOIN ?:company_descriptions AS company_descriptions'
            . ' ON company_descriptions.company_id = companies.company_id'
            . ' AND company_descriptions.lang_code = ?s',
            $lang_code
        );
    }

    $condition = fn_get_company_condition('companies.company_id');

    /**
     * Hook for changing parameters before SQL query
     *
     * @param int    $company_id Company ID
     * @param string $lang_code  2-letter language code (e.g. 'en', 'ru', etc.)
     * @param array  $extra      Array with extra parameters
     * @param array  $fields     Array with tables fields for SQL query
     * @param string $join       String with SQL join statements
     * @param string $condition  String with conditions for the WHERE SQL statement
     */
    fn_set_hook('get_company_data', $company_id, $lang_code, $extra, $fields, $join, $condition);

    $company_data = db_get_row(
        'SELECT ' . implode(', ', $fields) . ' FROM ?:companies AS companies ?p'
        . ' WHERE companies.company_id = ?i ?p',
        $join,
        $company_id,
        $condition
    );

    if ($company_data) {
        $company_data['category_ids'] = !empty($company_data['categories']) ? explode(',', $company_data['categories']) : array();
        $company_data['shippings_ids'] = !empty($company_data['shippings']) ? explode(',', $company_data['shippings']) : array();
        $company_data['countries_list'] = !empty($company_data['countries_list']) ? explode(',', $company_data['countries_list']) : array();
    }

    /**
     * Hook for changing result of function
     *
     * @param int    $company_id   Company ID
     * @param string $lang_code    2-letter language code (e.g. 'en', 'ru', etc.)
     * @param array  $extra        Array with extra parameters
     * @param array  $company_data Array with company data
     */
    fn_set_hook('get_company_data_post', $company_id, $lang_code, $extra, $company_data);

    if (empty($extra['skip_cache'])) {
        $company_data_cache[$cache_key] = $company_data;
    }

    return $company_data;
}

/**
 * Gets object's company ID value for given object from thegiven table.
 * Function checks is some object has the given company ID.
 *
 * @param string $table Table name
 * @param string $field Field name
 * @param mixed $field_value Value of given field
 * @param mixed $company_id Company ID for additional condition.
 * @return mixed Company ID or false, if check fails.
 */
function fn_get_company_id($table, $field, $field_value, $company_id = '')
{
    $condition = ($company_id !== '') ? db_quote(' AND company_id = ?i ', $company_id) : '';

    $id = db_get_field("SELECT company_id FROM ?:$table WHERE $field = ?s $condition", $field_value);

    return ($id !== NULL) ? $id : false;
}

/**
 * Gets company ID for the given company name
 *
 * @staticvar array $companies Little static cache for company ids
 * @param string $company_name Company name
 * @return integer Company ID or null, if company name was not found.
 */
function fn_get_company_id_by_name($company_name)
{
    static $companies = array();

    if (!empty($company_name)) {
        if (empty($companies[md5($company_name)])) {

            $condition = db_quote(' AND company = ?s', $company_name);

            /**
             * Hook get_company_id_by_name is executing before selecting the company ID by name.
             *
             * @param string $company_name Company name
             * @param string $condition 'Where' condition of SQL query
             */
            fn_set_hook('get_company_id_by_name', $company_name, $condition);

            $companies[md5($company_name)] = db_get_field("SELECT company_id FROM ?:companies WHERE 1 $condition");
        }

        return $companies[md5($company_name)];
    }

    return false;
}

function fn_get_available_company_ids($company_ids = array())
{
    $condition = '';
    if ($company_ids) {
        $condition = db_quote(' AND company_id IN (?a)', $company_ids);
    }

    return db_get_fields("SELECT company_id FROM ?:companies WHERE 1 ?p AND status IN ('A', 'P', 'N')", $condition);
}

function fn_check_company_id($table, $key, $key_id, $company_id = '')
{
    if (!Registry::get('runtime.company_id')) {
        return true;
    }

    if ($company_id === '') {
        $company_id = Registry::get('runtime.company_id');
    }

    $id = db_get_field("SELECT $key FROM ?:$table WHERE $key = ?i AND company_id = ?i", $key_id, $company_id);

    return (!empty($id)) ? true : false;
}

/**
 * Function checks is given object is shared for selected store.
 *
 * @param string $object Name of object
 * @param int $object_id Object ID
 * @param int $company_id Company ID, if empty, value of Registry::get('runtime.company_id') will be used
 * @return boolean true if ojbect is shared for given company_id, false otherwise
 */
function fn_check_shared_company_id($object, $object_id, $company_id = '')
{
    if ($company_id === '') {
        if (!Registry::get('runtime.company_id')) {
            return true;
        }

        $company_id = Registry::get('runtime.company_id');
    }

    $id = db_get_field("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = ?s AND share_object_id = ?i AND share_company_id = ?i", $object, $object_id, $company_id);

    return (!empty($id)) ? true : false;
}

/**
 * Function checks is given object is shared for given stores.
 *
 * @param string $object Name of object
 * @param int $object_id Object ID
 * @param array $company_ids Company IDs
 * @return boolean true if ojbect is shared for given company_ids, false otherwise
 */
function fn_check_shared_company_ids($object, $object_id, $company_ids = array())
{
    if (empty($company_ids)) {
        return false;
    }

    $id = db_get_field("SELECT share_object_id FROM ?:ult_objects_sharing WHERE share_object_type = ?s AND share_object_id = ?i AND share_company_id IN (?a)", $object, $object_id, $company_ids);

    return (!empty($id)) ? true : false;
}

/**
 * Set company_id to actual company_id
 *
 * @param mixed $data Array with data
 */
function fn_set_company_id(&$data, $key_name = 'company_id', $only_defined = false)
{
    if (Registry::get('runtime.company_id')) {
        $data[$key_name] = Registry::get('runtime.company_id');
    } elseif (!isset($data[$key_name]) && !fn_allowed_for('ULTIMATE') && !$only_defined) {
        $data[$key_name] = 0;
    }
}

function fn_payments_set_company_id($order_id = 0, $company_id = 0, $area = AREA)
{
    if ($area != 'A' && fn_allowed_for('ULTIMATE')) {
        if (!empty($order_id)) {
            $company_id = db_get_field("SELECT company_id FROM ?:orders WHERE order_id = ?i", $order_id);
        }
        Registry::set('runtime.company_id', $company_id);
    }
}

function fn_get_companies_shipping_ids($company_id)
{
    static $company_shippings;

    if (isset($company_shippings[$company_id])) {
        return $company_shippings[$company_id];
    }

    $shippings = array();

    $companies_shippings = explode(',', db_get_field("SELECT shippings FROM ?:companies WHERE company_id = ?i", $company_id));
    $default_shippings = db_get_fields("SELECT shipping_id FROM ?:shippings WHERE company_id = ?i", $company_id);
    $shippings = array_merge($companies_shippings, $default_shippings);

    $company_shippings[$company_id] = $shippings;

    return $shippings;
}

function fn_update_company($company_data, $company_id = 0, $lang_code = CART_LANGUAGE)
{
    $can_update = true;

    /**
     * Update company data (running before fn_update_company() function)
     *
     * @param array   $company_data Company data
     * @param int     $company_id   Company identifier
     * @param string  $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param boolean $can_update   Flag, allows addon to forbid to create/update company
     */
    fn_set_hook('update_company_pre', $company_data, $company_id, $lang_code, $can_update);

    if ($can_update == false) {
        return false;
    }

    if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
        unset($company_data['comission'], $company_data['comission_type'], $company_data['categories'], $company_data['shippings']);
    } elseif (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        unset($company_data['storefront'], $company_data['secure_storefront']);
    }

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {

        if (isset($company_data['storefront'])) {
            if (empty($company_data['storefront'])) {
                fn_set_notification('E', __('error'), __('storefront_url_not_defined'));

                return false;

            } else {
                if (empty($company_data['secure_storefront'])) {
                    $company_data['secure_storefront'] = $company_data['storefront'];
                }

                $company_data['storefront'] = Url::clean($company_data['storefront']);
                $company_data['secure_storefront'] = Url::clean($company_data['secure_storefront']);
            }
        }
    }

    unset($company_data['company_id']);
    $_data = $company_data;

    if (fn_allowed_for('MULTIVENDOR')) {
        // Check if company with same email already exists
        $is_exist = db_get_field("SELECT email FROM ?:companies WHERE company_id != ?i AND email = ?s", $company_id, $_data['email']);
        if (!empty($is_exist)) {
            $_text = 'error_vendor_exists';
            fn_set_notification('E', __('error'), __($_text));

            return false;
        }
    }

    if (fn_allowed_for('ULTIMATE') && !empty($company_data['storefront'])) {
        // Check if company with the same Storefront URL already exists
        $http_exist = db_get_row('SELECT company_id, storefront FROM ?:companies WHERE storefront = ?s', $company_data['storefront']);
        $https_exist = db_get_row('SELECT company_id, secure_storefront FROM ?:companies WHERE secure_storefront = ?s', $company_data['secure_storefront']);

        if (!empty($http_exist) || !empty($https_exist)) {
            if (empty($company_id)) {
                if (!empty($http_exist)) {
                    fn_set_notification('E', __('error'), __('storefront_url_already_exists'));
                } else {
                    fn_set_notification('E', __('error'), __('secure_storefront_url_already_exists'));
                }

                return false;

            } elseif ((!empty($http_exist) && $company_id != $http_exist['company_id']) || (!empty($https_exist) && $company_id != $https_exist['company_id'])) {

                if (!empty($http_exist) && $company_id != $http_exist['company_id']) {
                    fn_set_notification('E', __('error'), __('storefront_url_already_exists'));
                    unset($_data['storefront']);
                } else {
                    fn_set_notification('E', __('error'), __('secure_storefront_url_already_exists'));
                    unset($_data['secure_storefront']);
                }

                return false;
            }
        }
    }

    if (isset($company_data['shippings'])) {
        $_data['shippings'] = empty($company_data['shippings']) ? '' : fn_create_set($company_data['shippings']);
    }

    if (!empty($_data['countries_list'])) {
        $_data['countries_list'] = implode(',', $_data['countries_list']);
    } else {
        $_data['countries_list'] = '';
    }

    // add new company
    if (empty($company_id)) {
        // company title can't be empty
        if (empty($company_data['company'])) {
            fn_set_notification('E', __('error'), __('error_empty_company_name'));

            return false;
        }

        $_data['timestamp'] = TIME;

        $company_id = db_query("INSERT INTO ?:companies ?e", $_data);

        if (empty($company_id)) {
            return false;
        }

        $_data['company_id'] = $company_id;

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:company_descriptions ?e", $_data);
        }

        $action = 'add';

    // update company information
    } else {
        if (isset($company_data['company']) && empty($company_data['company'])) {
            unset($company_data['company']);
        }

        if (!empty($_data['status'])) {
            $status_from = db_get_field("SELECT status FROM ?:companies WHERE company_id = ?i", $company_id);
        }
        db_query("UPDATE ?:companies SET ?u WHERE company_id = ?i", $_data, $company_id);

        if (isset($status_from) && $status_from != $_data['status']) {
            fn_companies_change_status($company_id, $_data['status'], '', $status_from, true);
        }

        // unset data lang code as it determines company main language not description language
        unset($_data['lang_code']);
        db_query(
            "UPDATE ?:company_descriptions SET ?u WHERE company_id = ?i AND lang_code = ?s",
            $_data, $company_id, $lang_code
        );

        $action = 'update';
    }

    /**
     * Update company data (running after fn_update_company() function)
     *
     * @param array  $company_data Company data
     * @param int    $company_id   Company integer identifier
     * @param string $lang_code    Two-letter language code (e.g. 'en', 'ru', etc.)
     * @param string $action       Flag determines if company was created (add) or just updated (update).
     */
    fn_set_hook('update_company', $company_data, $company_id, $lang_code, $action);

    $logo_ids = array();

    if ($action == 'add') {
        $theme_name = !empty($company_data['theme_name']) ? $company_data['theme_name'] : Registry::get('config.base_theme');

        if (fn_allowed_for('ULTIMATE')) {
            $clone_from = !empty($company_data['clone_from']) && $company_data['clone_from'] != 'all' ? $company_data['clone_from'] : null;

            if (!is_null($clone_from)) {
                $theme_name = fn_get_theme_path('[theme]', 'C', $clone_from);
            }
        }

        if (fn_allowed_for('ULTIMATE')) {
            $logo_ids = fn_install_theme($theme_name, $company_id, false);
        } else {
            $logo_ids = fn_create_theme_logos_by_layout_id($theme_name, 0, $company_id, true);
        }
    }

    fn_attach_image_pairs('logotypes', 'logos', 0, $lang_code, $logo_ids);

    return $company_id;
}

function fn_companies_filter_company_product_categories(&$request, &$product_data)
{
    if (fn_allowed_for('MULTIVENDOR')) {
        if (Registry::get('runtime.company_id')) {
            $company_id = Registry::get('runtime.company_id');
        } elseif (isset($product_data['company_id'])) {
            $company_id = $product_data['company_id'];
        } elseif (!empty($product_data['product_id'])) {
            $company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_data['product_id']);
        } else {
            return false;
        }

        $company_data = fn_get_company_data($company_id);

        if (empty($company_data['category_ids'])) {
            // all categories are allowed
            return true;
        }

        if (!empty($request['category_id']) && !in_array($request['category_id'], $company_data['category_ids'])) {
            unset($request['category_id']);
            $changed = true;
        }
        if (!empty($product_data['main_category']) && !in_array($product_data['main_category'], $company_data['category_ids'])) {
            unset($product_data['main_category']);
            $changed = true;
        }
        if (!empty($product_data['category_ids'])) {
            $categories = explode(',', $product_data['category_ids']);
            foreach ($categories as $k => $v) {
                if (!in_array($v, $company_data['category_ids'])) {
                    unset($categories[$k]);
                    $changed = true;
                }
            }
            $product_data['category_ids'] = implode(',', $categories);
        }
    }

    return empty($changed);
}

function fn_delete_company($company_id)
{
    if (empty($company_id)) {
        return false;
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        // Do not delete vendor if there're any orders associated with this company
        if (db_get_field("SELECT COUNT(*) FROM ?:orders WHERE company_id = ?i", $company_id)) {
            fn_set_notification('W', __('warning'), __('unable_delete_vendor_orders_exists'), '', 'company_has_orders');

            return false;
        }
    }

    fn_set_hook('delete_company_pre', $company_id);

    $result = db_query("DELETE FROM ?:companies WHERE company_id = ?i", $company_id);

    // deleting categories
    $cat_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE company_id = ?i", $company_id);
    foreach ($cat_ids as $cat_id) {
        fn_delete_category($cat_id, false);
    }

    // deleting products
    $product_ids = db_get_fields("SELECT product_id FROM ?:products WHERE company_id = ?i", $company_id);
    foreach ($product_ids as $product_id) {
        fn_delete_product($product_id);
    }

    // deleting shipping
    $shipping_ids = db_get_fields("SELECT shipping_id FROM ?:shippings WHERE company_id = ?i", $company_id);
    foreach ($shipping_ids as $shipping_id) {
        fn_delete_shipping($shipping_id);
    }

    if (fn_allowed_for('ULTIMATE')) {
        // deleting layouts
        $layouts = Layout::instance($company_id)->getList();
        foreach ($layouts as $layout_id => $layout) {
            Layout::instance($company_id)->delete($layout_id);
        }
    }

    $blocks = Block::instance($company_id)->getAllUnique();
    foreach ($blocks as $block) {
        Block::instance($company_id)->remove($block['block_id']);
    }

    $product_tabs = ProductTabs::instance($company_id)->getList();
    foreach ($product_tabs as $product_tab) {
        ProductTabs::instance($company_id)->delete($product_tab['tab_id'], true);
    }

    $_menus = Menu::getList(db_quote(" AND company_id = ?i" , $company_id));
    foreach ($_menus as $menu) {
        Menu::delete($menu['menu_id']);
    }


    db_query("DELETE FROM ?:company_descriptions WHERE company_id = ?i", $company_id);

    // deleting product_options
    $option_ids = db_get_fields("SELECT option_id FROM ?:product_options WHERE company_id = ?i", $company_id);
    foreach ($option_ids as $option_id) {
        fn_delete_product_option($option_id);
    }

    // deleting company admins and users
    if (Registry::get('settings.Stores.share_users') != 'Y') {
        $users_condition = db_quote(' OR company_id = ?i', $company_id);
    } else {
        $users_condition = '';

        // Unassign users from deleted company
        db_query('UPDATE ?:users SET company_id = 0 WHERE company_id = ?i', $company_id);
    }

    $user_ids = db_get_fields("SELECT user_id FROM ?:users WHERE company_id = ?i AND user_type = ?s ?p", $company_id, 'V', $users_condition);
    foreach ($user_ids as $user_id) {
        fn_delete_user($user_id);
    }

    // deleting pages
    $page_ids = db_get_fields("SELECT page_id FROM ?:pages WHERE company_id = ?i", $company_id);
    foreach ($page_ids as $page_id) {
        fn_delete_page($page_id);
    }

    // deleting promotions
    $promotion_ids = db_get_fields("SELECT promotion_id FROM ?:promotions WHERE company_id = ?i", $company_id);
    fn_delete_promotions($promotion_ids);

    // deleting features
    $feature_ids = db_get_fields("SELECT feature_id FROM ?:product_features WHERE company_id = ?i", $company_id);
    foreach ($feature_ids as $feature_id) {
        fn_delete_feature($feature_id);
    }

    // deleting logos
    $types = fn_get_logo_types();
    foreach ($types as $type => $data) {
        fn_delete_logo($type, $company_id);
    }

    $payment_ids = db_get_fields('SELECT payment_id FROM ?:payments WHERE company_id = ?i', $company_id);
    foreach ($payment_ids as $payment_id) {
        fn_delete_payment($payment_id);
    }

    // Delete sitemap sections and links
    $params = array(
        'company_id' => $company_id,
    );
    $section_ids = fn_get_sitemap_sections($params);
    fn_delete_sitemap_sections(array_keys($section_ids));

    fn_set_hook('delete_company', $company_id, $result);

    return $result;
}

function fn_chown_company($from, $to)
{
    // Only allow the superadmin to merge vendors

    if (empty($from) || empty($to) || !isset($_SESSION['auth']['is_root']) || $_SESSION['auth']['is_root'] != 'Y' || Registry::get('runtime.company_id')) {
        return false;
    }

    // Chown & disable vendor's admin accounts
    db_query("UPDATE ?:users SET status = 'D', company_id = ?i WHERE company_id = ?i AND user_type = 'V'", $to, $from);

    $config = Registry::get('config');
    $tables = db_get_fields("SELECT INFORMATION_SCHEMA.COLUMNS.TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE INFORMATION_SCHEMA.COLUMNS.COLUMN_NAME = 'company_id' AND TABLE_SCHEMA = ?s;", $config['db_name']);

    foreach ($tables as $table) {
        $table = str_replace(Registry::get('config.table_prefix'), '', $table);
        if ($table != 'companies' && $table != 'company_descriptions') {
            db_query("UPDATE ?:$table SET company_id = ?i WHERE company_id = ?i", $to, $from);
        }
    }

    return true;
}

/**
 * Function returns address of company and emails of company' departments.
 *
 * @param integer $company_id ID of company
 * @param string $lang_code Language of retrieving data. If null, lang_code of company will be used.
 * @return array Company address, emails and lang_code.
 */
function fn_get_company_placement_info($company_id, $lang_code = null)
{
    $default_company_placement_info = Registry::get('settings.Company');

    if (empty($company_id)) {
        $company_placement_info = $default_company_placement_info;
        $company_placement_info['lang_code'] = !empty($lang_code) ? $lang_code : CART_LANGUAGE;
    } else {
        $company = fn_get_company_data($company_id, (!empty($lang_code) ? $lang_code : CART_LANGUAGE));

        if (fn_allowed_for('ULTIMATE')) {
            $company_placement_info = Settings::instance()->getValues('Company', Settings::CORE_SECTION, true, $company_id);
            $default_company_placement_info = $company_placement_info;
            $company_placement_info['lang_code'] = $company['lang_code'];
        } else {
            $company_placement_info = array(
                'company_name' => $company['company'],
                'company_address' => $company['address'],
                'company_city' => $company['city'],
                'company_country' => $company['country'],
                'company_state' => $company['state'],
                'company_zipcode' => $company['zipcode'],
                'company_phone' => $company['phone'],
                'company_phone_2' => '',
                'company_fax' => $company['fax'],
                'company_website' => $company['url'],
                'company_users_department' => $company['email'],
                'company_site_administrator' => $company['email'],
                'company_orders_department' => $company['email'],
                'company_support_department' => $company['email'],
                'company_newsletter_email' => $company['email'],
                'lang_code' => $company['lang_code'],
            );
        }
    }

    foreach ($default_company_placement_info as $k => $v) {
        $company_placement_info['default_' . $k] = $v;
    }

    $lang_code = !empty($lang_code) ? $lang_code : $company_placement_info['lang_code'];

    $company_placement_info['company_country_descr'] = fn_get_country_name($company_placement_info['company_country'], $lang_code);
    $company_placement_info['company_state_descr'] = fn_get_state_name($company_placement_info['company_state'], $company_placement_info['company_country'], $lang_code);

    return $company_placement_info;
}

function fn_get_company_language($company_id)
{
    if (empty($company_id)) {
        return Registry::get('settings.Appearance.backend_default_language');
    } else {
        $company = fn_get_company_data($company_id);

        return $company['lang_code'];
    }
}

/**
 * Fucntion changes company status. Allowed statuses are A(ctive) and D(isabled)
 *
 * @param int $company_id
 * @param string $status_to A or D
 * @param string $reason The reason of the change
 * @param string $status_from Previous status
 * @param boolean $skip_query By default false. Update query might be skipped if status is already changed.
 * @return boolean True on success or false on failure
 */
function fn_companies_change_status($company_id, $status_to, $reason = '', &$status_from = '', $skip_query = false, $notify = true)
{
    if (empty($status_from)) {
        $status_from = db_get_field("SELECT status FROM ?:companies WHERE company_id = ?i", $company_id);
    }

    if (!in_array($status_to, array('A', 'P', 'D')) || $status_from == $status_to) {
        return false;
    }

    $result = $skip_query ? true : db_query("UPDATE ?:companies SET status = ?s WHERE company_id = ?i", $status_to, $company_id);

    if (!$result) {
        return false;
    }

    $company_data = fn_get_company_data($company_id);

    $account = $username = '';
    if ($status_from == 'N' && ($status_to == 'A' || $status_to == 'P')) {
        if (Registry::get('settings.Vendors.create_vendor_administrator_account') == 'Y') {
            if (!empty($company_data['request_user_id'])) {
                $password_change_timestamp = db_get_field("SELECT password_change_timestamp FROM ?:users WHERE user_id = ?i", $company_data['request_user_id']);
                $_set = '';
                if (empty($password_change_timestamp)) {
                    $_set = ", password_change_timestamp = 1 ";
                }
                db_query("UPDATE ?:users SET company_id = ?i, user_type = 'V'$_set WHERE user_id = ?i", $company_id, $company_data['request_user_id']);

                $username = fn_get_user_name($company_data['request_user_id']);
                $account = 'updated';

                $msg = __('new_administrator_account_created') . '<a href="' . fn_url('profiles.update?user_id=' . $company_data['request_user_id']) . '">' . __('you_can_edit_account_details') . '</a>';
                fn_set_notification('N', __('notice'), $msg, 'K');

            } else {
                $user_data = array();

                if (!empty($company_data['request_account_name'])) {
                    $user_data['user_login'] = $company_data['request_account_name'];
                } else {
                    $user_data['user_login'] = $company_data['email'];
                }

                $request_account_data = unserialize($company_data['request_account_data']);
                $user_data['fields'] = $request_account_data['fields'];
                $user_data['firstname'] = $user_data['b_firstname'] = $user_data['s_firstname'] = $request_account_data['admin_firstname'];
                $user_data['lastname'] = $user_data['b_lastname'] = $user_data['s_lastname'] = $request_account_data['admin_lastname'];

                $user_data['user_type'] = 'V';
                $user_data['password1'] = fn_generate_password();
                $user_data['password2'] = $user_data['password1'];
                $user_data['status'] = 'A';
                $user_data['company_id'] = $company_id;
                $user_data['email'] = $company_data['email'];
                $user_data['company'] = $company_data['company'];
                $user_data['last_login'] = 0;
                $user_data['lang_code'] = $company_data['lang_code'];
                $user_data['password_change_timestamp'] = 0;

                // Copy vendor admin billing and shipping addresses from the company's credentials
                $user_data['b_address'] = $user_data['s_address'] = $company_data['address'];
                $user_data['b_city'] = $user_data['s_city'] = $company_data['city'];
                $user_data['b_country'] = $user_data['s_country'] = $company_data['country'];
                $user_data['b_state'] = $user_data['s_state'] = $company_data['state'];
                $user_data['b_zipcode'] = $user_data['s_zipcode'] = $company_data['zipcode'];

                list($added_user_id, $null) = fn_update_user(0, $user_data, $null, false,  false);

                if ($added_user_id) {
                    $msg = __('new_administrator_account_created') . '<a href="' . fn_url('profiles.update?user_id=' . $added_user_id) . '">' . __('you_can_edit_account_details') . '</a>';
                    fn_set_notification('N', __('notice'), $msg, 'K');

                    $username = $user_data['user_login'];
                    $account = 'new';
                }
            }
        }
    }

    if (empty($user_data)) {
        $user_id = db_get_field("SELECT user_id FROM ?:users WHERE company_id = ?i AND is_root = 'Y' AND user_type = 'V'", $company_id);
        $user_data = fn_get_user_info($user_id);
    }

    if ($notify && !empty($company_data['email'])) {

        $e_username = '';
        $e_account = '';
        $e_password = '';

        if ($status_from == 'N' && ($status_to == 'A' || $status_to == 'P')) {
            $e_username = $username;
            $e_account = $account;
            if ($account == 'new') {
                $e_password = $user_data['password1'];
            }
        }

        $mail_template = fn_strtolower($status_from . '_' . $status_to);

        Mailer::sendMail(array(
            'to' => $company_data['email'],
            'from' => 'default_company_support_department',
            'data' => array(
                'user_data' => $user_data,
                'reason' => $reason,
                'status' => __($status_to == 'A' ? 'active' : 'disabled'),
                'e_username' => $e_username,
                'e_account' => $e_account,
                'e_password' => $e_password
            ),
            'company_id' => $company_id,
            'tpl' => 'companies/status_' . $mail_template . '_notification.tpl'
        ), 'A');
    }

    return $result;
}

function fn_get_company_by_product_id($product_id)
{
    return db_get_row("SELECT * FROM ?:companies AS com LEFT JOIN ?:products AS prod ON com.company_id = prod.company_id WHERE prod.product_id = ?i", $product_id);
}

function fn_get_companies_sorting()
{
    $sorting = array(
        'company' => array('description' => __('name'), 'default_order' => 'asc'),
    );

    fn_set_hook('companies_sorting', $sorting);

    return $sorting;
}

function fn_get_companies_sorting_orders()
{
    return array('asc', 'desc');
}

/**
 * Gets ids of all companies
 *
 * @staticvar array $all_companies_ids Static cache variable
 * @param boolean $renew_cache If defined, cache of companies ids will be renewed.
 * @return array Ids of all companies
 */
function fn_get_all_companies_ids($renew_cache = false)
{
    static $all_companies_ids = null;

    if ($all_companies_ids === null || $renew_cache) {
        $all_companies_ids = db_get_fields("SELECT company_id FROM ?:companies");
    }

    return $all_companies_ids;
}

function fn_get_default_company_id()
{
    return db_get_field("SELECT company_id FROM ?:companies WHERE status = 'A' ORDER BY company_id LIMIT 1");
}

function fn_set_data_company_id(&$data)
{
    if (fn_allowed_for('ULTIMATE')) {
        $data['company_id'] = Registry::get('runtime.company_id');
    }
}

function fn_get_ult_company_condition($db_field = 'company_id', $and = true, $company_id = '', $show_admin = false, $area_c = false)
{
    return (fn_allowed_for('ULTIMATE')) ? fn_get_company_condition($db_field, $and, $company_id, $show_admin, $area_c) : '';
}
