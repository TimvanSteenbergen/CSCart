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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'async') {
    $company_id = fn_se_get_company_id();
    if (empty($_REQUEST['parent_private_key']) || fn_se_get_parent_private_key($company_id, DEFAULT_LANGUAGE) !== $_REQUEST['parent_private_key']) {
        $check_key = false;
    } else {
        $check_key = true;
    }

    @ignore_user_abort(1);
    @set_time_limit(0);
    if ($check_key && $_REQUEST['display_errors'] === 'Y') {
        @error_reporting (E_ALL);
        @ini_set('display_errors', 1);
    } else {
        @ini_set('display_errors', 0);
    }

    if (defined('SE_MEMORY_LIMIT')) {
        if (substr(ini_get('memory_limit'), 0, -1) < SE_MEMORY_LIMIT) {
            @ini_set('memory_limit', SE_MEMORY_LIMIT . 'M');
        }
    }
    $fl_ignore_processing = false;
    if ($check_key && $_REQUEST['ignore_processing'] === 'Y') {
        $fl_ignore_processing = true;
    }

    $q = fn_se_get_next_queue();

    fn_echo('.');

    $json_header = fn_se_get_json_header();

    while (!empty($q)) {
        if (fn_se_check_debug()) {
            fn_print_r($q);
        }
        $xml = '';
        $status = true;
        $company_id = $q['company_id'];
        $lang_code  = $q['lang_code'];
        $data = unserialize($q['data']);
        $private_key = fn_se_get_private_key($company_id, $lang_code);

        if (empty($private_key)) {
            db_query("DELETE FROM ?:se_queue WHERE queue_id = ?i", $q['queue_id']);
            $q = array();
            continue;
        }

        //Note: $q['started'] can be in future.
        if ($q['status'] == 'processing' && ($q['started'] + SE_MAX_PROCESSING_TIME > TIME)) {
            if (!$fl_ignore_processing) {
                die('PROCESSING');
            }
        }

        if ($q['error_count'] >= SE_MAX_ERROR_COUNT) {
            fn_se_set_import_status('sync_error', $company_id, $lang_code);
            die('DISABLED');
        }

        // Set queue to processing state
        db_query("UPDATE ?:se_queue SET `status` = 'processing', `started` = ?s WHERE queue_id = ?i", TIME, $q['queue_id']);

        if ($q['action'] == 'prepare_full_import') {

            db_query("DELETE FROM ?:se_queue WHERE action != 'prepare_full_import' AND company_id = ?i AND lang_code = ?s", $company_id, $lang_code);

            db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES ('N;', 'start_full_import', '{$company_id}', '{$lang_code}')");

            $i = 0;
            $step = SE_PRODUCTS_PER_PASS * 50;

            $sqls_arr = array();

            $min_max = db_get_row('SELECT MIN(`product_id`) as min, MAX(`product_id`) as max FROM ?:products');

            $start = (int) $min_max['min'];
            $max   = (int) $min_max['max'];

            do {
                $end = $start + $step;

                $_product_ids = db_get_fields('SELECT product_id FROM ?:products WHERE product_id >= ?i AND product_id <= ?i LIMIT ?i', $start, $end, $step);

                $start = $end + 1;

                if (empty($_product_ids)) {
                    continue;
                }
                $_product_ids = array_chunk($_product_ids, SE_PRODUCTS_PER_PASS);

                foreach ($_product_ids as $product_ids) {
                    $sqls_arr[] = "('" . serialize($product_ids) . "', 'update', '{$company_id}', '{$lang_code}')";
                }

                if (count($sqls_arr) >= 30) {
                    db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES " . join(',', $sqls_arr));
                    fn_echo('.');
                    $sqls_arr = array();
                }

            } while ($end <= $max);

            if (count($sqls_arr) > 0) {
                db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES " . join(',', $sqls_arr));
            }

            fn_echo('.');

            //
            // reSend all active filters
            //

            if (!fn_allowed_for('ULTIMATE:FREE') && fn_se_get_setting('use_navigation', $company_id, DEFAULT_LANGUAGE) == 'Y') {
                db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES ('N;', 'facet_delete_all', '{$company_id}', '{$lang_code}')");

                list($filters, ) = fn_get_product_filters(array(
                    'get_descriptions' => false,
                    'get_variants' => false,
                    'status' => 'A'
                ));

                if (!empty($filters)) {

                    foreach ($filters as $filter) {
                        $filter_ids[] = $filter['filter_id'];
                    }

                    db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES (?s, 'facet_update', '{$company_id}', '{$lang_code}')", serialize($filter_ids));
                }
            }

            db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES ('N;', 'pages_update', '{$company_id}', '{$lang_code}')");
            db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES ('N;', 'categories_update', '{$company_id}', '{$lang_code}')");

            db_query("INSERT INTO ?:se_queue (`data`, `action`, `company_id`, `lang_code`) VALUES ('N;', 'end_full_import', '{$company_id}', '{$lang_code}')");

            $status = true;

        } elseif ($q['action'] == 'start_full_import') {

            $status = fn_se_send_request('/api/state/update/json', $private_key, array('full_import' => 'start'));

            if ($status == true) {
                fn_se_set_import_status('processing', $company_id, $lang_code);
            }

        } elseif ($q['action'] == 'end_full_import') {
            $status = fn_se_send_request('/api/state/update/json', $private_key, array('full_import' => 'done'));

            if ($status == true) {
                fn_se_set_import_status('sent', $company_id, $lang_code);
                fn_se_set_simple_setting('last_resync', TIME);
            }

        } elseif ($q['action'] == 'categories_update') {
            $data = fn_se_get_categories_data($data, $company_id, $lang_code);

            if (!empty($data)) {
                $data = json_encode(array_merge($json_header, array('categories' => $data)));
                $status = fn_se_send_request('/api/items/update/json', $private_key, array('data' => $data));
            }

        } elseif ($q['action'] == 'pages_update') {
            $data = fn_se_get_pages_data($data, $company_id, $lang_code);

            if (!empty($data)) {
                $data = json_encode(array_merge($json_header, array('pages' => $data)));
                $status = fn_se_send_request('/api/items/update/json', $private_key, array('data' => $data));
            }

        } elseif ($q['action'] == 'facet_update') {
            list($filters, ) = fn_get_product_filters(array(
                'filter_id' => $filter_ids,
                'get_variants' => true
            ), 0, $lang_code);

            $facets = array();
            foreach ($filters as $filter_data) {
                $facets[] = fn_se_prepare_facet_data($filter_data);
            }

            if (!empty($facets)) {
                $data = json_encode(array_merge($json_header, array('schema' => $facets)));
                $status = fn_se_send_request('/api/items/update/json', $private_key, array('data' => $data));
            }

        } elseif ($q['action'] == 'update') {
            $data = fn_se_get_products_data($data, $company_id, $lang_code, true);

            if (!empty($data)) {
                $data = json_encode(array_merge($json_header, $data));

                if (function_exists('gzcompress')) {
                    $_data = gzcompress($data, 5);
                    if (!empty($_data)) {//workaround for some servers
                        $data = $_data;
                    }
                }

                $status = fn_se_send_request('/api/items/update/json', $private_key, array('data' => $data));
            }

        } elseif ($q['action'] == 'facet_delete_all') {
            $status = fn_se_send_request('/api/facets/delete/json', $private_key, array('all' => true));

        } elseif ($q['action'] == 'delete' || $q['action'] == 'categories_delete' || $q['action'] == 'pages_delete' || $q['action'] == 'facet_delete') {
            if ($q['action'] == 'delete') {
                $type = 'items';
            } elseif($q['action'] == 'categories_delete') {
                $type = 'categories';
            } elseif($q['action'] == 'pages_delete') {
                $type = 'pages';
            } elseif($q['action'] == 'facet_delete') {
                $type = 'facets';
            }

            foreach ($data as $id) {
                $status = fn_se_send_request("/api/{$type}/delete/json", $private_key, ($q['action'] == 'facet_delete')? array('attribute' => $id) : array('id' => $id));
                fn_echo('.');
                if ($status == false) {
                    break;
                }
            }

        } elseif ($q['action'] == 'delete_all') {
            $status = fn_se_send_request('/api/items/delete/json', $private_key, array('all' => true));

        }

        if (fn_se_check_debug()) {
            fn_print_r('status', $status);
        }

        // Change queue item status
        if ($status == true) {
            db_query("DELETE FROM ?:se_queue WHERE queue_id = ?i", $q['queue_id']);// Done, cleanup queue

            $q = fn_se_get_next_queue($q['queue_id']);

        } else {
            $next_started_time = (TIME - SE_MAX_PROCESSING_TIME) + $q['error_count'] * 60;

            db_query("UPDATE ?:se_queue SET status = 'processing', error_count = error_count + 1, started = ?s WHERE queue_id = ?i", $next_started_time, $q['queue_id']);

            break; //try later
        }
        fn_echo('.');
    }

    die('OK');
}

if ($mode == 'info') {
    fn_se_check_import_is_done();
    $company_id = fn_se_get_company_id();
    $engines_data = fn_se_get_engines_data($company_id, NULL, true);
    $options = array();

    if (empty($_REQUEST['parent_private_key']) || fn_se_get_parent_private_key($company_id, DEFAULT_LANGUAGE) !== $_REQUEST['parent_private_key']) {
        foreach ($engines_data as $e) {
            $options[$e['company_id']][$e['lang_code']] = $e['api_key'];
        }
    } else {
        if (isset($_REQUEST['product_id'])) {
            $lang_code = DEFAULT_LANGUAGE;
            if (isset($_REQUEST['lang_code'])) {
                $lang_code = $_REQUEST['lang_code'];
            } elseif (isset($_REQUEST['sl'])) {
                $lang_code = $_REQUEST['sl'];
            }
            
            $options = fn_se_get_products_xml($_REQUEST['product_id'], $company_id, $lang_code, false);

        } elseif (isset($_REQUEST['resync']) && $_REQUEST['resync'] === 'Y') {
            fn_se_signup(NULL, NULL, true);
            fn_se_queue_import(NULL, NULL, true);

        } else {
            $options = $engines_data;
            if (!$options) {
                $options = array();
            }

            $options['core_edition'] = PRODUCT_NAME;
            $options['core_version'] = PRODUCT_VERSION;
            $options['core_status'] = PRODUCT_STATUS;
            $options['core_build'] = PRODUCT_BUILD;

            $options['next_queue'] = fn_se_get_next_queue();
            $options['total_items_in_queue'] = fn_se_get_total_items_queue();

            $options['max_execution_time'] = ini_get('max_execution_time');
            @set_time_limit(0);
            $options['max_execution_time_after'] = ini_get('max_execution_time');

            $options['ignore_user_abort'] = ini_get('ignore_user_abort');
            @ignore_user_abort(1);
            $options['ignore_user_abort_after'] = ini_get('ignore_user_abort_after');

            $options['memory_limit'] = ini_get('memory_limit');
            if (defined('SE_MEMORY_LIMIT')) {
                if (substr(ini_get('memory_limit'), 0, -1) < SE_MEMORY_LIMIT) {
                    @ini_set('memory_limit', SE_MEMORY_LIMIT . 'M');
                }
            }
            $options['memory_limit_after'] = ini_get('memory_limit');
        }
    }

    if (isset($_REQUEST['output'])) {
        fn_echo(json_encode($options));
    } else {
        fn_print_r($options);
    }

    die();
}

function fn_se_get_total_items_queue()
{
    $total_items = 0;

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $total_items = db_get_field('SELECT COUNT(queue_id) FROM ?:se_queue WHERE company_id = ?i', Registry::get('runtime.company_id'));
    } elseif (!fn_allowed_for('ULTIMATE')) {
        $total_items = db_get_field('SELECT COUNT(queue_id) FROM ?:se_queue WHERE 1');
    }

    return $total_items;
}

function fn_se_get_next_queue($queue_id = 0)
{
    $q = array();
    $conditions = '';

    if (empty($queue_id)) {
        $conditions .= db_quote(' AND queue_id > ?i', $queue_id);
    }

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $q = db_get_row("SELECT * FROM ?:se_queue WHERE company_id = ?i $conditions ORDER BY queue_id ASC LIMIT 1", Registry::get('runtime.company_id'));
    } elseif (!fn_allowed_for('ULTIMATE')) {
        $q = db_get_row("SELECT * FROM ?:se_queue WHERE 1 $conditions ORDER BY queue_id ASC LIMIT 1");
    }

    return $q;
}

function fn_se_get_pages_data($pages_ids = array(), $company_id = 0, $lang_code)
{
    list($pages,) = fn_get_pages(array(
        'status'   => 'A',
        'item_ids' => join(',', $pages_ids),
    ), 0, $lang_code);

    $data = array();
    foreach($pages as $page) {
        $page_id = $page['page_id'];
        $data[] = array(
            'id'      => $page_id,
            'link'    => ($page['page_type'] == PAGE_TYPE_LINK)? fn_url($page['link']) : fn_url('pages.view?page_id=' . $page_id, 'C', 'http', $lang_code),
            'title'   => $page['page'],
            'summary' => $page['description'],
        );
    }

    return $data;
}

function fn_se_get_categories_data($categories_ids = array(), $company_id = 0, $lang_code)
{
    list($categories,) = fn_get_categories(array(
        'plain'    => true,
        'simple'   => false,
        'status'   => 'A',
        'item_ids' => join(',', $categories_ids),
        'group_by_level' => false,
    ), $lang_code);

    $data = array();
    $categories_ids = fn_se_get_ids($categories, 'category_id');
    $categories_data = db_get_hash_array("SELECT c.category_id, cd.description FROM ?:categories AS c LEFT JOIN ?:category_descriptions AS cd ON cd.category_id = c.category_id AND cd.lang_code = ?s WHERE c.category_id IN (?n)", 'category_id', $lang_code, $categories_ids);
    foreach($categories as &$category) {
        $category_id = $category['category_id'];
        if (!isset($categories_data[$category_id])) {
            $categories_data[$category_id] = array();
        }

        $category  = array_merge($category, array('description' => ''), $categories_data[$category_id]);

        $data[] = array(
            'id'        => $category['category_id'],
            'parent_id' => $category['parent_id'],
            'link'      => fn_url('categories.view?category_id=' . $category['category_id'], 'C', 'http', $lang_code),
            'title'     => $category['category'],
            'summary'   => $category['description'],
        );
    }
    return $data;
}

function fn_se_get_products_data($product_ids, $company_id = 0, $lang_code = NULL, $fl_echo = true)
{
    $xml = '';
    $products = array();

    if (!empty($product_ids)) {
        list($products) = fn_get_products(array(
            'disable_searchanise' => true,
            'area'    => 'A',
            'sort_by' => 'null',
            'pid'     => $product_ids,
            'extend'  => array('description', 'search_words', 'popularity', 'sales', 'categories_filter'),
        ), 0, $lang_code);
    }

    if ($fl_echo) {
        fn_echo('.');
    }

    if (!empty($products)) {
        foreach ($products as &$_product) {
            $_product['exclude_from_calculate'] = true; //pass additional params to fn_gather_additional_products_data for some speed up
        }

        fn_gather_additional_products_data($products, array(
            'get_features' => false,
            'get_icon' => true,
            'get_detailed' => true,
            'get_options'=> false,
            'get_discounts' => false,
            'get_taxed_prices' => false
        ));

        if ($fl_echo) {
            fn_echo('.');
        }

        if (!fn_allowed_for('ULTIMATE:FREE')) {
            $usergroups = empty($usergroups) ? array_merge(fn_get_default_usergroups(), db_get_hash_array("SELECT a.usergroup_id, a.status, a.type FROM ?:usergroups as a WHERE a.type = 'C' ORDER BY a.usergroup_id", 'usergroup_id')) : $usergroups;
        } else {
            $usergroups = array();
        }

        fn_se_get_products_additionals($products, $company_id, $lang_code);

        fn_se_get_products_features($products, $company_id, $lang_code);

        $schema = $items = array();
        foreach ($products as $product) {
            $item = array();
            $data = fn_se_prepare_product_data($product, $usergroups, $company_id, $lang_code);
            foreach($data as $name => $d) {
                $name = isset($d['name'])? $d['name'] : $name;
                $item[$name] = $d['value'];
                unset($d['value']);
                if (!empty($d)) {
                    $schema[$name] = $d;
                }
            }
            $items[] = $item;
        }
    }

    return array(
        'schema' => $schema,
        'items'  => $items
    );
}

function fn_se_get_products_features(&$products, $company_id, $lang_code)
{
    $product_ids = fn_se_get_ids($products, 'product_id');

    $features_data = db_get_array("SELECT v.feature_id, v.value, v.value_int, v.variant_id, f.feature_type, fd.description as feature, vd.variant, v.product_id FROM ?:product_features_values as v LEFT JOIN ?:product_features as f ON f.feature_id = v.feature_id LEFT JOIN ?:product_features_descriptions as fd ON fd.feature_id = f.feature_id AND fd.lang_code = ?s LEFT JOIN ?:product_feature_variants fv ON fv.variant_id = v.variant_id LEFT JOIN ?:product_feature_variant_descriptions as vd ON vd.variant_id = fv.variant_id AND vd.lang_code = ?s WHERE v.product_id IN (?n) AND (v.variant_id != 0 OR (f.feature_type != 'C' AND v.value != '') OR (f.feature_type = 'C') OR v.value_int != '') AND v.lang_code = ?s", $lang_code, $lang_code, $product_ids, $lang_code);

    if (!empty($features_data)) {
        foreach ($features_data as $_data) {
            $product_id = $_data['product_id'];
            $feature_id = $_data['feature_id'];

            if (empty($products_features[$product_id][$feature_id])) {
                $products_features[$product_id][$feature_id] = $_data;
            }

            if (!empty($_data['variant_id'])) { // feature has several variants
                $products_features[$product_id][$feature_id]['variants'][$_data['variant_id']] = $_data;
            }
        }

        foreach ($products as &$product) {
            $product['product_features'] = isset($products_features[$product['product_id']]) ? $products_features[$product['product_id']] : array();
        }
    }
}

function fn_se_get_products_additionals(&$products, $company_id, $lang_code)
{
    $product_ids = fn_se_get_ids($products, 'product_id');

    if (fn_allowed_for('ULTIMATE')) {
        $shared_prices = db_get_hash_multi_array('SELECT product_id, (IF(percentage_discount = 0, price, price - (price * percentage_discount)/100)) as price, usergroup_id FROM ?:ult_product_prices WHERE company_id = ?i AND product_id IN (?n) AND lower_limit = 1', array('product_id', 'usergroup_id'), $company_id, $product_ids);
        $prices = db_get_hash_multi_array('SELECT product_id, (IF(percentage_discount = 0, price, price - (price * percentage_discount)/100)) as price, usergroup_id FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit = 1', array('product_id', 'usergroup_id'), $product_ids);
        $product_categories = db_get_hash_multi_array("SELECT pc.product_id, c.category_id, c.usergroup_ids, c.status FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON c.category_id = pc.category_id WHERE c.company_id = ?i AND product_id IN (?n) AND c.status IN ('A', 'H')", array('product_id', 'category_id'), $company_id, $product_ids);
        $shared_descriptions = db_get_hash_array("SELECT product_id, full_description FROM ?:ult_product_descriptions WHERE company_id = ?i AND product_id IN (?n) AND lang_code = ?s", 'product_id', $company_id, $product_ids, $lang_code);
    } else {
        $prices = db_get_hash_multi_array('SELECT product_id, (IF(percentage_discount = 0, price, price - (price * percentage_discount)/100)) as price, usergroup_id FROM ?:product_prices WHERE product_id IN (?n) AND lower_limit = 1', array('product_id', 'usergroup_id'), $product_ids);
        $product_categories = db_get_hash_multi_array("SELECT pc.product_id, c.category_id, c.usergroup_ids, c.status FROM ?:categories AS c LEFT JOIN ?:products_categories AS pc ON c.category_id = pc.category_id WHERE product_id IN (?n) AND c.status IN ('A', 'H')", array('product_id', 'category_id'), $product_ids);
    }

    if (Registry::get('settings.General.inventory_tracking') == 'Y' && Registry::get('settings.General.show_out_of_stock_products') == 'N') {
        $product_options = db_get_hash_single_array("SELECT product_id, max(amount) as amount FROM ?:product_options_inventory WHERE product_id IN (?n) GROUP BY product_id", array('product_id', 'amount'), $product_ids);
    }

    $descriptions = db_get_hash_array("SELECT product_id, full_description FROM ?:product_descriptions WHERE 1 AND product_id IN (?n) AND lang_code = ?s", 'product_id', $product_ids, $lang_code);

    foreach ($products as &$product) {
        $product_id = $product['product_id'];

        if (isset($shared_prices[$product_id])) {
            $product['se_prices'] = $shared_prices[$product_id];
        } elseif (isset($prices[$product_id])) {
            $product['se_prices'] = $prices[$product_id];
        } else {
            $product['se_prices'] = array('0' => array('price' => 0));
        }

        if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS && isset($product_options[$product_id])) {
            $product['amount'] = $product_options[$product_id];
        }

        if (!empty($shared_descriptions[$product_id]['full_description'])) {
            $product['se_full_description'] = $shared_descriptions[$product_id]['full_description'];
        } elseif (!empty($descriptions[$product_id]['full_description'])) {
            $product['se_full_description'] = $descriptions[$product_id]['full_description'];
        }

        $product['category_ids'] = array();
        $product['category_usergroup_ids'] = array();

        if (!empty($product_categories[$product_id])) {
            foreach ($product_categories[$product_id] as $pc) {
                $product['category_ids'][] = $pc['category_id'];
                $product['category_usergroup_ids'] = array_merge($product['category_usergroup_ids'], explode(',', $pc['usergroup_ids']));
            }
        }

        $product['empty_categories'] = (empty($product['category_ids'])) ? 'Y' : 'N';
    }
}

function fn_se_prepare_product_data($product_data, $usergroups, $company_id, $lang_code)
{
    $types_map = array(
        'D' => 'int',  // timestamp  (others -> date)
        'M' => 'text', // multicheckbox with enter other input
        'S' => 'text', // select text with enter other input
        'N' => 'float',  // select number with enter other input
        'E' => 'text', // extended
        'C' => 'text', // single checkbox (not avilable for filter)
        'T' => 'text', // input  (others -> text) (not avilable for filterering)
        'O' => 'float',  // input for number (others -> number)
    );

    $entry = array(
        'id' => array(
            'value' => $product_data['product_id'],
            'title' => __('se_product_id'),
        ),
        'title' => array(
            'value' => $product_data['product'],
            'title' => __('se_title'),
        ),
        'summary' => array(
            'value' => (!empty($product_data['short_description']) ? $product_data['short_description'] : $product_data['full_description']),
            'title' => __('se_summary'),
        ),
        'link' => array(
            'value' => fn_url('products.view?product_id=' . $product_data['product_id'], 'C', 'http', $lang_code),
            'title' => __('se_link'),
        ),
        'price'  => array(
            'value' => fn_format_price($product_data['price']),
            'title' => __('se_price'),
        ),
        'quantity' => array(
            'value' => $product_data['amount'],
            'title' => __('se_quantity'),
        ),
        'product_code' => array(
            'value' => $product_data['product_code'],
            'title' => __('se_product_code'),
        ),
        'image_link' => array(
            'title' => __('se_image_link'),
        ),
   );

    if (!empty($product_data['main_pair'])) {
        $thumbnail = fn_image_to_display($product_data['main_pair'], SE_IMAGE_SIZE, SE_IMAGE_SIZE);
    }

    if (!empty($thumbnail['image_path'])) {
        $image_link = $thumbnail['image_path'];

    } elseif (!empty($product_data['main_pair']['detailed']['http_image_path'])) {
        $image_link = $product_data['main_pair']['detailed']['http_image_path'];

    } else {
        $image_link = '';
    }

    $entry['image_link']['value'] = htmlspecialchars($image_link);

    if (!empty($product_data['search_words'])) {
        $entry['search_words'] = array(
            'name'        => 'search_words',
            'title'       => __('search_words'),
            'text_search' => 'Y',
            'weight'      => 100,
            'value'       => $product_data['search_words'],
        );
    }

    if (!empty($product_data['product_features'])) {
        foreach ($product_data['product_features'] as $f) {
            $name = "f_{$f['feature_id']}";
            $entry[$name] = array(
                'name'        => $name,
                'title'       => $f['feature'],
                'text_search' => 'Y',
                'weight'      => 60,
            );

            if ($f['feature_type'] == 'S' || $f['feature_type'] == 'E') {
                $entry[$name]['value'] = $f['variant'];
            } else {
                $entry[$name]['value'] = $f['value'];
            }
        }
    }

    if (!empty($product_data['short_description']) && !empty($product_data['se_full_description'])) {
        $entry['full_description'] = array(
            'name'        => 'full_description',
            'title'       => __('full_description'),
            'text_search' => 'Y',
            'weight'      => 40,
        );
        $entry['full_description']['value'] = $product_data['se_full_description'];
    }

    if (!empty($product_data['product_features']) && fn_se_get_setting('use_navigation', $company_id, DEFAULT_LANGUAGE) == 'Y') {
        foreach ($product_data['product_features'] as $f) {
            if ($f['feature_type'] == 'G') {
                continue;
            }

            $name = "feature_{$f['feature_id']}";
            $entry[$name] = array(
                'name'  => $name,
                'type'  => $types_map[$f['feature_type']],
                'title' => str_replace('[id]', $f['feature'], __("se_for_feature_id")),
            );

            if ($f['feature_type'] == 'M') {
                if (!empty($f['variants']) && is_array($f['variants'])) {
                    foreach ($f['variants'] as $fv) {
                        $entry[$name]['value'][] = $fv['variant_id'];
                    }
                }
            } else {
                if ($f['feature_type'] == 'S' || $f['feature_type'] == 'E') {
                    $entry[$name]['value'] = $f['variant_id'];

                } elseif ($f['feature_type'] == 'N') {
                    $entry[$name]['value'] = $f['variant'];

                } elseif ($f['feature_type'] == 'O' || $f['feature_type'] == 'D') {
                    $entry[$name]['value'] = $f['value_int'];

                } elseif ($f['feature_type'] == 'C') {
                    $entry[$name]['value'] = ($f['value'] == 'Y')? 'Y' : 'N';

                } else {// T
                    $entry[$name]['value'] = $f['value'];
                }
            }
        }
    }

    //
    //
    //
    $entry['category_id'] = array(
        'name'  => 'category_id',
        'title' => __('se_category_Id'),
        'value' => array()
    );
    foreach ($product_data['category_ids'] as $category_id) {
        $entry['category_id']['value'][] = $category_id;
    }

    //
    //
    //
    $entry['category_usergroup_ids'] = array(
        'name'  => 'category_usergroup_ids',
        'title' => __('se_category_usergroup_ids'),
    );
    foreach ($product_data['category_usergroup_ids'] as $usergroup_id) {
        $entry['category_usergroup_ids']['value'][] = $usergroup_id;
    }

    //
    //
    //
    $entry['usergroup_ids'] = array(
        'name'  => 'usergroup_ids',
        'title' => __('se_usergroup_ids'),
    );
    $product_data['usergroup_ids'] = empty($product_data['usergroup_ids'])? array(0) : explode(',', $product_data['usergroup_ids']);
    foreach ($product_data['usergroup_ids'] as $usergroup_id) {
        $entry['usergroup_ids']['value'][] = $usergroup_id;
    }

    //
    //
    //
    foreach ($usergroups as $usergroup) {
        $usergroup_id = $usergroup['usergroup_id'];
        $price = (!empty($product_data['se_prices'][$usergroup_id]['price'])) ? $product_data['se_prices'][$usergroup_id]['price'] : $product_data['se_prices'][0]['price'];
        $name = 'price_'.intval($usergroup_id);
        $entry[$name] = array(
            'name'  => $name,
            'title' => str_replace('[id]', $usergroup_id, __("se_price_for_usergroup_id")),
            'type'  => 'float',
            'value' => $price,
        );
    }

    //
    //
    //
    $additional_attrs = array(
        'company_id'       => 'text',
        'weight'           => 'float',
        'popularity'       => 'float',
        'amount'           => 'int',
        'timestamp'        => 'int',
        'position'         => 'int',
        'free_shipping'    => 'text',
        'empty_categories' => 'text',
        'status'           => 'text',
   );

    if (!empty($product_data['sales_amount'])) {
        $additional_attrs['sales_amount'] = 'int';
    }

    foreach ($additional_attrs as $name => $type) {
        if ($name == 'company_id') {
            $title = __('se_company_id');
        } else if ($name == 'empty_categories') {
            $title = __('se_empty_categories');
        } else {
            $title = __($name);
        }

        $entry[$name] = array(
            'name'  => $name,
            'title' => $title,
            'type'  => $type,
            'value' => $product_data[$name]
        );
    }

    return $entry;
}

function fn_se_prepare_facet_data($filter_data)
{
    $entry = array();
    if (!empty($filter_data['feature_id'])) {
        $entry['name'] = "feature_{$filter_data['feature_id']}";

    } elseif (!empty($filter_data['field_type']) && $filter_data['field_type'] == 'P') {
        $entry['name'] = "price";

    } elseif (!empty($filter_data['field_type']) && $filter_data['field_type'] == 'F') {
        $entry['name'] = "free_shipping";

    } elseif (!empty($filter_data['field_type']) && $filter_data['field_type'] == 'S') {
        $entry['name'] = "company_id";

    } elseif (!empty($filter_data['field_type']) && $filter_data['field_type'] == 'A') {
        $entry['name'] = "amount";

    } else {
        return array(); //unknown attribute
    }

    $entry['facet']['title'] = $filter_data['filter'];
    $entry['facet']['position'] = $filter_data['position'];

    $filter_fields = fn_get_product_filter_fields();
    if (!empty($filter_fields[$filter_data['field_type']]['slider'])) {
        $entry['facet']['type'] = "slider";
    }

    if ((!empty($filter_data['feature_type']) && strpos('ODN', $filter_data['feature_type']) !== false) || (!empty($filter_data['field_type']) && !empty($filter_fields[$filter_data['field_type']]['is_range']))) {
        $entry['ranges'] = array();

        foreach ($filter_data['ranges'] as $k => $r) {
            if (!empty($filter_data['feature_type']) && $filter_data['feature_type'] == 'D' && !empty($filter_data['dates_ranges'][$k])) {
                $r['to'] = fn_parse_date($filter_data['dates_ranges'][$k]['to']);
                $r['from'] = fn_parse_date($filter_data['dates_ranges'][$k]['from']);
            }
            if (!empty($r['range_name'])) {
                $entry['ranges'][] = array(
                    'title' => $r['range_name'],
                    'from'       => $r['from'],
                    'to'         => $r['to'],
                    'position'   => $r['position'],
                );
            }
        }
    }

    return $entry;
}
