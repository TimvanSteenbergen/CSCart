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

/**
 * Gets products default navigation
 *
 * @param array $params Request params
 * @return array navigation data
 */
function fn_lv_get_product_default_navigation($params)
{
    if (empty($params['product_id'])) {
        return false;
    }

    $update_data = array();

    $product_id = $params['product_id'];

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $company_condition = fn_get_company_condition('c.company_id');
        $category_id = db_get_field("SELECT c.category_id, IF(pc.link_type = ?s, 1, 0) as is_main FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON c.category_id = pc.category_id WHERE pc.product_id = ?i $company_condition ORDER BY is_main DESC" , 'M', $product_id);
    } else {
        $category_id = db_get_field("SELECT category_id FROM ?:products_categories WHERE product_id = ?i AND link_type = ?s", $product_id, 'M');
    }

    if (empty($category_id)) {
        return false;
    }

    $search_params = array (
        'cid' => $category_id,
        'get_conditions' => true
    );

    list($fields, $join, $condition) = fn_get_products($search_params);
    $sorting = "ORDER BY descr1.product asc";

    // get product position in full list
    db_query("SET @r = 0;");
    $product_position = db_get_field("SELECT a.row FROM (SELECT products.product_id, @r := @r + 1 as row FROM ?:products as products $join WHERE 1 $condition GROUP BY products.product_id $sorting) AS a WHERE a.product_id = ?i", $product_id);

    $items_per_page = Registry::get('settings.Appearance.products_per_page');

    if (empty($product_position) || empty($items_per_page)) {
        return false;
    }

    $page = ceil($product_position / $items_per_page);
    $limit = db_paginate($page, $items_per_page);

    $stored_items_ids[$page] = db_get_fields("SELECT SQL_CALC_FOUND_ROWS products.product_id FROM ?:products as products $join WHERE 1 $condition GROUP BY products.product_id $sorting $limit");
    $total_items = db_get_found_rows();
    $total_pages = ceil($total_items / $items_per_page);

    unset($search_params['get_conditions']);

    $update_data['params'] = serialize($search_params);
    $update_data['view_results'] = array(
        'items_ids' => $stored_items_ids,
        'total_pages' => $total_pages,
        'items_per_page' => $items_per_page,
        'total_items' => $total_items,
    );

    $update_data['view_results'] = serialize($update_data['view_results']);

    return $update_data;
}
