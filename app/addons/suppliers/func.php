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

use Tygh\Mailer;
use Tygh\Registry;
use Tygh\Navigation\LastView;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Update supplier data
 *
 * @param int $supplier_id
 * @param array $supplier_data
 * @return int Supplier id
 */
function fn_update_supplier($supplier_id, $supplier_data)
{
    $old_supplier_data = fn_get_supplier_data($supplier_id);

    if (empty($supplier_id)) {
        $supplier_id = db_query('INSERT INTO ?:suppliers ?e', $supplier_data);
    } else {
        db_query('UPDATE ?:suppliers SET ?u WHERE supplier_id = ?i', $supplier_data, $supplier_id);
    }

    // Update supplier shipping methods
    $shippings = empty($supplier_data['shippings']) ? array() : $supplier_data['shippings'];
    fn_update_supplier_shippings($supplier_id, $shippings);

    $hidden_products = array();
    if (!empty($old_supplier_data['products'])) {
        $all_products = fn_get_all_supplier_products($supplier_id);
        $hidden_products = array_diff($all_products, $old_supplier_data['products']);

        if ($hidden_products) {
            $supplier_data['products'] .= ',' . implode(',', $hidden_products);
        }
    }

    // Update supplier products
    $products = empty($supplier_data['products']) ? array() : explode(',', $supplier_data['products']);
    fn_update_supplier_products($supplier_id, $products);

    return $supplier_id;
}

/**
 * Update supplier shippings links
 *
 * @param int $supplier_id
 * @param array $shippings
 * @return bool Always true
 */
function fn_update_supplier_shippings($supplier_id, $shippings)
{
    db_query('DELETE FROM ?:supplier_links WHERE object_type = ?s AND supplier_id = ?i', 'S', $supplier_id);

    if (!empty($shippings)) {
        $parts = array();

        foreach ($shippings as $shipping_id) {
            $parts[] = db_quote('(?i, ?i, ?s)', $supplier_id, $shipping_id, 'S');
        }

        if (!empty($parts)) {
            $query = 'INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES ' . implode(',', $parts);
            db_query($query);
        }
    }

    return true;
}

/**
 * Update supplier products links
 *
 * @param int $supplier_id
 * @param array $products
 * @return bool Always true
 */
function fn_update_supplier_products($supplier_id, $products)
{
    db_query('DELETE FROM ?:supplier_links WHERE object_type = ?s AND supplier_id = ?i', 'P', $supplier_id);

    if (!empty($products)) {
        $parts = array();

        foreach ($products as $product_id) {
            $parts[] = db_quote('(?i, ?i, ?s)', $supplier_id, $product_id, 'P');
        }

        if (!empty($parts)) {
            $query = 'INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES ' . implode(',', $parts);
            db_query($query);
        }
    }

    return true;
}

/**
 * Get supplier data
 *
 * @param array $params
 * @return array Found suppliers data
 */
function fn_get_suppliers($params = array(), $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('suppliers', $params);

    $condition = fn_get_company_condition('?:suppliers.company_id');
    $join = db_quote(" JOIN ?:companies ON ?:suppliers.company_id = ?:companies.company_id");

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        "?:suppliers.supplier_id",
        "?:suppliers.timestamp",
        "?:suppliers.status",
        "?:suppliers.name",
        "?:suppliers.email",
        "?:suppliers.company_id",
        "?:companies.company as company_name",
    );

    // Define sort fields
    $sortings = array (
        'id' => "?:suppliers.supplier_id",
        'email' => "?:suppliers.email",
        'name' => "?:suppliers.name",
        'date' => "?:suppliers.timestamp",
        'type' => "?:suppliers.supplier_type",
        'status' => "?:suppliers.status",
        'company' => "company_name",
    );

    $filters = array(
        'name' => "?:suppliers.name",
        'email' => "?:suppliers.email",
        'address' => "?:suppliers.address",
        'zipcode' => "?:suppliers.zipcode",
        'country' => "?:suppliers.country",
        'state' => "?:suppliers.state",
        'city' => "?:suppliers.city",
        'status' => "?:suppliers.status",
        'company' => "?:companies.company",
    );

    foreach ($filters as $filter => $field) {
        if (!empty($params[$filter])) {
            $condition .= db_quote(" AND " . $field . " LIKE ?l", "%" . trim($params[$filter]) . "%");
        }
    }

    if (!empty($params['supplier_id'])) {
        $condition .= db_quote(' AND ?:suppliers.supplier_id IN (?n)', $params['supplier_id']);
    }

    $sorting = db_sort($params, $sortings, 'name', 'asc');

    // Paginate search results
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:suppliers.supplier_id)) FROM ?:suppliers ?p WHERE 1 ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $suppliers = db_get_array("SELECT ?p FROM ?:suppliers ?p WHERE 1 ?p GROUP BY ?:suppliers.supplier_id ?p ?p", implode(', ', $fields), $join, $condition, $sorting, $limit);

    LastView::instance()->processResults('suppliers', $suppliers, $params);

    return array($suppliers, $params);
}

/**
 * Get supplier data
 *
 * @param int $supplier_id
 * @return array Found supplier data and shippings links and products links
 */
function fn_get_supplier_data($supplier_id)
{
    $supplier = db_get_row('SELECT * FROM ?:suppliers WHERE supplier_id = ?i', $supplier_id);
    if (!empty($supplier)) {
        $supplier['shippings'] = db_get_fields('SELECT object_id FROM ?:supplier_links WHERE object_type = ?s AND supplier_id = ?i', 'S', $supplier_id);

        $condition = $join =  $group = "";
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $join .= db_quote(" INNER JOIN ?:products_categories ON ?:supplier_links.object_id = ?:products_categories.product_id");
            $join .= db_quote(" INNER JOIN ?:categories ON ?:products_categories.category_id = ?:categories.category_id ");
            $condition .= db_quote(" AND ?:categories.company_id = ?i ", Registry::get('runtime.company_id'));
            $group .= ' GROUP BY product_id';
        }
        $supplier['products'] = db_get_fields('SELECT object_id FROM ?:supplier_links ?p WHERE ?:supplier_links.object_type = ?s AND ?:supplier_links.supplier_id = ?i ?p ?p', $join, 'P', $supplier_id, $condition, $group);
    }

    return !empty($supplier) ? $supplier : false;
}

/**
 * Get all supplier products
 *
 * @param int $supplier_id
 * @return array
 */
function fn_get_all_supplier_products($supplier_id)
{
    return db_get_fields('SELECT object_id FROM ?:supplier_links WHERE ?:supplier_links.object_type = \'P\' AND ?:supplier_links.supplier_id = ?i', $supplier_id);
}

/**
 * Get supplier name
 *
 * @param int $supplier_id
 * @return string Found supplier name
 */
function fn_get_supplier_name($supplier_id)
{
    if (!empty($supplier_id)) {
        $supplier_name = db_get_field("SELECT ?:suppliers.name FROM ?:suppliers WHERE ?:suppliers.supplier_id = ?i", $supplier_id);
    }

    return !empty($supplier_name) ? $supplier_name : __('none');
}

/**
 * Get supplier ID from product ID
 *
 * @param int $product_id
 * @return int Found supplier ID
 */
function fn_get_product_supplier_id($product_id)
{
    if (!empty($product_id)) {
        $join = " LEFT JOIN ?:supplier_links ON ?:supplier_links.supplier_id = ?:suppliers.supplier_id AND ?:supplier_links.object_type = 'P' ";
        $supplier_id = db_get_field("SELECT ?:suppliers.supplier_id FROM ?:suppliers ?p WHERE ?:supplier_links.object_id = ?i", $join, $product_id);
    }

    return !empty($supplier_id) ? $supplier_id : false;
}

/**
 * Get supplier shippings
 *
 * @param int $supplier_id
 * @return array Found supplier shipping ids
 */
function fn_get_supplier_shippings($supplier_id)
{
    if (!empty($supplier_id)) {
        $shippings = db_get_fields('SELECT object_id FROM ?:supplier_links WHERE object_type = ?s AND supplier_id = ?i', 'S', $supplier_id);
    } else {
        $shippings = db_get_fields('SELECT object_id FROM ?:supplier_links WHERE object_type = ?s AND supplier_id = 0', 'S');
    }

    return !empty($shippings) ? $shippings : array();
}

/**
 * Gets list of linked suppliers
 *
 * @param int $shipping_id Shipping identifier
 * @return array List of linked suppliers
 */
function fn_get_shippings_suppliers($shipping_id)
{
    $supplier_ids = db_get_fields('SELECT supplier_id FROM ?:supplier_links WHERE object_type = ?s AND object_id = ?i', 'S', $shipping_id);

    return $supplier_ids;
}

/**
 * Sets links to suppliers
 *
 * @param int $shipping_id Shipping identifier
 * @param array $suppliers List of suppliers
 * @return bool always true
 */
function fn_set_shippings_suppliers($shipping_id, $suppliers)
{
    db_query('DELETE FROM ?:supplier_links WHERE object_id = ?i AND object_type = ?s', $shipping_id, 'S');

    foreach ($suppliers as $supplier_id => $enabled) {
        if ($enabled == 'Y') {
            db_query('INSERT INTO ?:supplier_links VALUES (?i, ?i, ?s)', $supplier_id, $shipping_id, 'S');
        }
    }

    return true;
}

/**
 * Delete supplier data
 *
 * @param int $supplier_id
 * @return bool
 */
function fn_delete_supplier($supplier_id)
{
    if (!empty($supplier_id)) {
        $result = db_query('DELETE FROM ?:suppliers WHERE supplier_id = ?i', $supplier_id);
        if ($result) {
            $result = db_query('DELETE FROM ?:supplier_links WHERE supplier_id = ?i', $supplier_id);
        }
    }

    return !empty($result) ? true : false;
}

/**
 * Update supplier status
 *
 * @param int $supplier_id
 * @param string $new_status
 * @return boolean
 */
function fn_update_status_supplier($supplier_id, $new_status)
{
    if (!empty($supplier_id)) {
        $result = db_query("UPDATE ?:suppliers SET status = ?s WHERE supplier_id = ?i", $new_status, $supplier_id);
    }

    return !empty($result) ? true : false;
}

/**
 * Get default supplier id
 *
 * @param int $company_id Supplier company_id
 * @return int Default supplier id
 */
function fn_get_default_supplier_id($company_id = 0)
{

    if (empty($company_id)) {
        $company_id = Registry::ifGet('runtime.company_id', fn_get_default_company_id());
    }

    return db_get_field("SELECT supplier_id FROM ?:suppliers WHERE status = 'A' AND company_id = ?i ORDER BY supplier_id LIMIT 1", $company_id);

}

/**
 * Get supplier data for supplier ID and company ID or get default supplier data for company ID
 *
 * @param int $supplier_id
 * @param int $company_id
 * @return array Found supplier data and shippings links and products links
 */
function fn_if_get_supplier($supplier_id, $company_id)
{
    if (fn_allowed_for('ULTIMATE')) {
        $condition = ''; // Use sharing instead
    } else {
        $condition = db_quote(' AND ?:suppliers.company_id = ?i', $company_id);
    }

    $supplier = db_get_row("SELECT * FROM ?:suppliers WHERE ?:suppliers.supplier_id = ?i ?p", $supplier_id, $condition);

    if (empty($supplier)) {
        if (fn_allowed_for('ULTIMATE')) {
            $condition = '';
        } else {
            $condition = db_quote('AND ?:suppliers.company_id = ?i', $company_id);
        }

        $count = db_get_field("SELECT COUNT(*) FROM ?:suppliers WHERE status = ?s ?p", 'A', $condition);
        if (!empty($count)) {
            $supplier = array('supplier_id' => 0, 'name' => '-' . __('none') . '-');
        }
    }

    return !empty($supplier) ? $supplier : false;
}

/**
 * Hook update product for update supplier_id
 *
 * @param array $product_data Product data
 * @param int $product_id Product id
 * @param string $lang_code Language code
 * @param bool $create Create or update
 * @return int Default supplier id
 */
function fn_suppliers_update_product_post(&$product_data, &$product_id, &$lang_code, &$create)
{
    if (isset($product_data['supplier_id']) && $product_data['supplier_id'] >= 0) {
        db_query("DELETE FROM ?:supplier_links WHERE object_type = ?s AND object_id = ?i", 'P', $product_id);
        if (!empty($product_data['supplier_id'])) {
            db_query("INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES (?i, ?i, ?s)", $product_data['supplier_id'], $product_id, 'P');
        }
    }
}

/**
 * Hook get product data for get supplier_id
 *
 * @param int $product_id Product ID
 * @param string $field_list List of fields for retrieving
 * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
 * @param mixed $auth Array with authorization data
 * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
 * @param string $condition Condition for selecting product data
 * @return int Default supplier id
 */
function fn_suppliers_get_product_data(&$product_id, &$field_list, &$join, &$auth, &$lang_code, &$condition)
{
    $field_list .= ", ?:supplier_links.supplier_id";
    $join .= " LEFT JOIN ?:supplier_links ON ?:supplier_links.object_id = ?:products.product_id AND ?:supplier_links.object_type = 'P' ";
}

function fn_suppliers_clone_product(&$product_id, &$pid)
{
    $clone_supplier = db_get_row('SELECT * FROM ?:supplier_links WHERE object_id = ?i', $product_id);
    $clone_supplier['object_id'] = $pid;
    db_query('INSERT INTO ?:supplier_links ?e', $clone_supplier);
}

/**
 * Hook get products for get supplier_id
 *
 * @param array  $params    Product search params
 * @param array  $fields    List of fields for retrieving
 * @param array  $sortings  Sorting fields
 * @param string $condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
 * @param string $join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
 * @param string $sorting   String containing the SQL-query ORDER BY clause
 * @param string $group_by  String containing the SQL-query GROUP BY field
 * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
 */
function fn_suppliers_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by, &$lang_code)
{
    $fields[] = "?:supplier_links.supplier_id";
    $join .= " LEFT JOIN ?:supplier_links ON ?:supplier_links.object_id = products.product_id AND ?:supplier_links.object_type = 'P' ";
    if (!empty($params['supplier_id'])) {
        $condition .= db_quote(" AND ?:supplier_links.supplier_id = ?i", $params['supplier_id']);
    }
}

/**
 * Hook for add field to product array
 *
 * @param array  $fields     Product fields
 */
function fn_suppliers_get_product_fields(&$fields)
{
    $fields[] = array(
        'name' => '[data][supplier_id]',
        'text' => __('supplier')
    );
}

/**
 * Hook get shipping info for get supplier id
 *
 * @param int $shipping_id Shipping ID
 * @param array $fields Fields array
 * @param string $join Join string
 * @param string $conditions Conditions string
 */
function fn_suppliers_get_shipping_info(&$shipping_id, &$fields, &$join, &$conditions)
{
    $fields[] = "?:supplier_links.supplier_id";
    $join .= " LEFT JOIN ?:supplier_links ON ?:supplier_links.object_id = ?:shippings.shipping_id AND ?:supplier_links.object_type = 'S' ";
}

/**
 * Hook update shipping for update supplier_id
 *
 * @param array $shipping_data Shipping data
 * @param int $shipping_id Shipping id
 * @param string $lang_code Language code
 */
function fn_suppliers_update_shipping_post(&$shipping_data, &$shipping_id, &$lang_code, &$action)
{
    if (!empty($shipping_data['supplier_id'])) {
        db_query("DELETE FROM ?:supplier_links WHERE object_type = ?s AND object_id = ?i", 'S', $shipping_id);
        db_query("INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES (?i, ?i, ?s)", $shipping_data['supplier_id'], $shipping_id, 'S');
    }

    if (isset($shipping_data['suppliers'])) {
        fn_set_shippings_suppliers($shipping_id, $shipping_data['suppliers']);

    } elseif ($action == 'add') {
        db_query("INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES (?i, ?i, ?s)", 0, $shipping_id, 'S');
    }
}

/**
 * Hook for modify shippings groups
 *
 * @param array $cart Cart array
 * @param array $cart_products Products from cart
 * @param array $auth Auth array
 * @param array $shipping_rates Shipping rates
 */
function fn_suppliers_shippings_group_products_list(&$products, &$groups)
{
    if (Registry::get('addons.suppliers.display_shipping_methods_separately') == 'N') {
        return;
    }

    $suppliers = array();
    $suppliers_groups = array();
    foreach ($groups as $group) {
        foreach ($group['products'] as $cart_id => $product) {
            $supplier_id = fn_get_product_supplier_id($product['product_id']);
            $suppliers_group_key = $supplier_id ? $group['company_id'] . "_" . $supplier_id : $group['company_id'];
            if (empty($suppliers_groups[$suppliers_group_key]) && $supplier_id) {
                $supplier_data = fn_get_supplier_data($supplier_id);
                $origination_data = array(
                    'name' => $supplier_data['name'],
                    'address' => $supplier_data['address'],
                    'city' => $supplier_data['city'],
                    'country' => $supplier_data['country'],
                    'state' => $supplier_data['state'],
                    'zipcode' => $supplier_data['zipcode'],
                    'phone' => $supplier_data['phone'],
                    'fax' => $supplier_data['fax'],
                );

                $suppliers_groups[$suppliers_group_key] = $group;
                $suppliers_groups[$suppliers_group_key]['supplier_id'] = $supplier_id;
                $suppliers_groups[$suppliers_group_key]['origination'] = $origination_data;
                $suppliers_groups[$suppliers_group_key]['name'] = $group['name'] . " (" . $supplier_data['name'] . ")";
                if (fn_allowed_for('ULTIMATE')) {
                    $suppliers_groups[$suppliers_group_key]['name'] = $supplier_data['name'];
                }

                $suppliers_groups[$suppliers_group_key]['products'] = array();
            }

            if (empty($suppliers_groups[$suppliers_group_key]) && !$supplier_id) {
                $suppliers_groups[$suppliers_group_key] = $group;
                $suppliers_groups[$suppliers_group_key]['products'] = array();
            }

            $suppliers_groups[$suppliers_group_key]['products'][$cart_id] = $product;
        }
    }

    ksort($suppliers_groups);
    $groups = array_values($suppliers_groups);
}

/**
 * Hook for modify shippings list
 *
 * @param array $cart Cart array
 * @param array $cart_products Products from cart
 * @param array $auth Auth array
 * @param array $shipping_rates Shipping rates
 */
function fn_suppliers_shippings_get_shippings_list(&$group, &$shippings)
{
    $supplier_id = isset($group['supplier_id']) ? $group['supplier_id'] : 0;

    $supplier_shippings = fn_get_supplier_shippings($supplier_id);
    $supplier_shippings = array_unique($supplier_shippings);

    $shippings = array_intersect($shippings, $supplier_shippings);

    if (Registry::get('addons.suppliers.display_shipping_methods_separately') == 'N') {
        foreach ($group['products'] as $cart_id => $product) {
            $supplier_id = fn_get_product_supplier_id($product['product_id']);
            $supplier_shippings = fn_get_supplier_shippings($supplier_id);
            $shippings = array_intersect($shippings, $supplier_shippings);
        }
    }

}

/**
 * Hook for modify shippings groups
 *
 * @param array $cart Cart array
 * @param array $allow
 * @param array $product_groups Products groups from cart
 */
function fn_suppliers_pre_place_order(&$cart, &$allow, &$product_groups)
{
    if (Registry::get('addons.suppliers.display_shipping_methods_separately') == 'N') {
        return;
    }

    $new_product_groups = array();
    foreach ($product_groups as $key_group => $group) {
        if (empty($new_product_groups[$group['company_id']])) {
            $new_product_groups[$group['company_id']] = $group;
            $new_product_groups[$group['company_id']]['name'] = fn_get_company_name($group['company_id']);
            $new_product_groups[$group['company_id']]['products'] = array();
            $new_product_groups[$group['company_id']]['chosen_shippings'] = array();
            if (!empty($group['supplier_id'])) {
                unset($new_product_groups[$group['company_id']]['supplier_id']);
            }
        }

        foreach ($group['products'] as $cart_id => $product) {
            $group['products'][$cart_id]['extra']['group_key'] = $key_group;
            $cart['products'][$cart_id]['extra']['group_key'] = $key_group;
        }

        if (!empty($group['supplier_id'])) {
            foreach ($group['products'] as $cart_id => $product) {
                $group['products'][$cart_id]['extra']['supplier_id'] = $group['supplier_id'];
                $cart['products'][$cart_id]['extra']['supplier_id'] = $group['supplier_id'];
            }
        }

        if (!empty($group['chosen_shippings'])) {
            $group['chosen_shippings'][0]['group_name'] = $group['name'];
            $new_product_groups[$group['company_id']]['chosen_shippings'] = array_merge($new_product_groups[$group['company_id']]['chosen_shippings'], $group['chosen_shippings']);
        }

        $new_product_groups[$group['company_id']]['products'] = $new_product_groups[$group['company_id']]['products'] + $group['products'];
    }

    $product_groups = array_values($new_product_groups);
}

/**
 * Hook for modify shipments
 *
 * @param array $shipments Shipments
 */
function fn_suppliers_get_shipments_info_post(&$shipments)
{
    if (!empty($shipments)) {
        $shipment = reset($shipments);
        $order_id = $shipment['order_id'];
        $order_info = fn_get_order_info($order_id);
        $group_supplier = array();

        if (!empty($order_info['products'])) {

            foreach ($order_info['products'] as $product_key => $product) {
                if (!empty($product['extra']['supplier_id'])) {
                    $group_supplier[$product['extra']['supplier_id']][$product_key] = $product['amount'];
                    foreach ($shipments as $key => $shipment) {
                        if (!empty($shipment['products'][$product_key])) {
                            $shipments[$key]['supplier_id'] = $product['extra']['supplier_id'];
                        }
                    }
                } else {
                    $group_supplier[0][$product_key] = $product['amount'];
                }
            }

            if (Settings::instance()->getValue('use_shipments', '', $order_info['company_id']) != 'Y') {

                $new_shipments = array();
                foreach ($shipments as $key => $shipment) {

                    $group_id = isset($shipment['supplier_id']) ? $shipment['supplier_id'] : 0;

                    $full_shipment = true;
                    foreach ($group_supplier[$group_id] as $product_key => $product_amount) {
                        if (empty($shipment['products'][$product_key]) || $shipment['products'][$product_key] < $product_amount) {
                            $full_shipment = false;
                        }
                    }

                    if ($full_shipment) {
                        $shipment['one_full'] = true;
                    }

                    $new_shipments[$key] = $shipment;
                }

                $shipments = $new_shipments;
            }
        }
    }
}

/**
 * Hook for modify shippings groups
 *
 * @param array $cart Cart array
 * @param array $allow
 * @param array $product_groups Products groups from cart
 */
function fn_suppliers_order_notification(&$order_info, &$order_statuses, &$force_notification)
{
    $status_params = $order_statuses[$order_info['status']]['params'];
    $notify_supplier = isset($force_notification['S']) ? $force_notification['S'] : (!empty($status_params['notify_supplier']) && $status_params['notify_supplier'] == 'Y' ? true : false);

    if ($notify_supplier == true) {

        $suppliers = array();

        if (!empty($order_info['product_groups'])) {
            foreach ($order_info['product_groups'] as $key_group => $group) {
                foreach ($group['products'] as $cart_id => $product) {
                    $supplier_id = fn_get_product_supplier_id($product['product_id']);
                    if (!empty($supplier_id) && empty($suppliers[$supplier_id])) {
                        $rate = 0;
                        foreach ($group['chosen_shippings'] as $shipping) {
                            $rate += $shipping['rate'];
                        }
                        $suppliers[$supplier_id] = array(
                            'name' => fn_get_supplier_name($supplier_id),
                            'company_id' => $group['company_id'],
                            'cost' => $rate,
                            'shippings' => $group['chosen_shippings'],
                        );
                    }
                    if (!empty($supplier_id)) {
                        $suppliers[$supplier_id]['products'][$cart_id] = $product;
                    }
                }
            }
        }

        foreach ($suppliers as $supplier_id => $supplier) {

            $lang = fn_get_company_language($supplier['company_id']);
            $order = $order_info;
            $order['products'] = $supplier['products'];

            $supplier['data'] = fn_get_supplier_data($supplier_id);

            if (!empty($supplier['shippings'])) {
                if (!empty($supplier['data']['shippings'])) {
                    $shippings = array();
                    foreach ($supplier['shippings'] as $shipping) {
                        if (!isset($shippings[$shipping['group_name']])) {
                            $shippings[$shipping['group_name']] = $shipping;
                        }
                    }

                    foreach ($shippings as $key => $shipping) {
                        if ($key != $supplier['name']) {
                            unset($shippings[$key]);
                            if ($supplier['cost'] > $shipping['rate']) {
                                $supplier['cost'] -= $shipping['rate'];
                            } else {
                                $supplier['cost'] = 0;
                            }
                        }
                    }

                    $supplier['shippings'] = array_values($shippings);
                } else {
                    $supplier['shippings'] = array();
                }
            }

            Mailer::sendMail(array(
                'to' => $supplier['data']['email'],
                'from' => 'company_orders_department',
                'reply_to' => 'company_orders_department',
                'data' => array(
                    'order_info' => $order,
                    'status_inventory' => $status_params['inventory'],
                    'supplier_id' => $supplier_id,
                    'supplier' => $supplier,
                    'order_status' => fn_get_status_data($order_info['status'], STATUSES_ORDER, $order_info['order_id'], $lang),
                    'profile_fields' => fn_get_profile_fields('I', '', $lang),
                ),
                'tpl' => 'addons/suppliers/notification.tpl'
            ), 'A', $lang);
        }
    }
}

function fn_suppliers_get_notification_rules(&$force_notification, &$params, &$disable_notification)
{
    if ($disable_notification) {
        $force_notification['S'] = false;
    } else {
        if (!empty($params['notify_supplier']) || $params === true) {
            $force_notification['S'] = true;
        } else {
            if (AREA == 'A') {
                $force_notification['S'] = false;
            }
        }
    }
}

function fn_suppliers_get_status_params_definition(&$status_params, &$type)
{
    if ($type == STATUSES_ORDER) {
        $status_params['notify_supplier'] = array (
            'type' => 'checkbox',
            'label' => 'notify_supplier',
        );
    }
}

function fn_suppliers_get_orders_post(&$params, &$orders)
{
    foreach ($orders as $key => $order) {
        $orders[$key]['have_suppliers'] = false;
        $product_ids = db_get_fields("SELECT ?:order_details.product_id FROM ?:order_details WHERE ?:order_details.order_id = ?i", $order['order_id']);
        foreach ($product_ids as $product_id) {
            if (fn_get_product_supplier_id($product_id)) {
                $orders[$key]['have_suppliers'] = true;
                break;
            }
        }
    }
}

function fn_suppliers_get_order_info(&$order, &$additional_data)
{
    if (!empty($order['products'])) {
        $order['have_suppliers'] = false;
        foreach ($order['products'] as $product) {
            if (fn_get_product_supplier_id($product['product_id'])) {
                $order['have_suppliers'] = true;
                break;
            }
        }
    }
}

/**
 * Executes actions when installing add-on
 */
function fn_suppliers_install()
{
    // Activate "None" supplier for all shippings
    $query_parts = array();
    $shippings = fn_get_shippings(true);

    foreach ($shippings as $shipping_id => $shipping_name) {
        $query_parts[] = db_quote('(?i, ?i, ?s)', 0, $shipping_id, 'S');
    }

    if (!empty($query_parts)) {
        db_query('INSERT INTO ?:supplier_links VALUES ' . implode(', ', $query_parts));
    }
}

/**
 * Links product with supplier
 *
 * @param int $supplier_id Supplier ID
 * @param int $product_id Product ID
 * @return bool Always true
 */
function fn_suppliers_link_product($supplier_id, $product_id)
{
    db_query('DELETE FROM ?:supplier_links WHERE object_type = ?s AND object_id = ?i', 'P', $product_id);
    db_query('INSERT INTO ?:supplier_links (supplier_id, object_id, object_type) VALUES (?i, ?i, ?s)', $supplier_id, $product_id, 'P');

    return true;
}

/**
 * Processes export field
 *
 * @param int $supplier_id
 * @return string Supplier name
 */
function fn_exim_get_supplier($product_id)
{
    $supplier_id = fn_get_product_supplier_id($product_id);

    return fn_get_supplier_name($supplier_id);
}

/**
 * Processes import field
 *
 * @param int $product_id Product ID
 * @param string $supplier_name Supplier name
 */
function fn_exim_put_supplier($product_id, $supplier_name)
{
    $supplier_id = db_get_field("SELECT supplier_id FROM ?:suppliers WHERE name = ?s", $supplier_name);

    fn_suppliers_link_product($supplier_id, $product_id);
}
