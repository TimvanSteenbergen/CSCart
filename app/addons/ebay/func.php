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
use Tygh\Session;
use Tygh\Settings;
use Tygh\Navigation\LastView;
use Ebay\Ebay;
use Tygh\Api\Entities\Orders;
use Tygh\Shippings\Shippings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once (Registry::get('config.dir.addons') . 'ebay/core.func.php');

function fn_check_cached_ebay_objects()
{
    if (AREA != 'A' || empty($_SESSION['auth']['user_id'])) {
        return false;
    }
    $token = Registry::get('addons.ebay.token');
    $app_id = Registry::get('addons.ebay.app_id');
    $dev_id = Registry::get('addons.ebay.dev_id');
    $cert_id = Registry::get('addons.ebay.cert_id');

    if (empty($token) || empty($app_id) || empty($dev_id) || empty($cert_id)) {
        return false;
    }

    set_time_limit (0);

    db_query('DELETE FROM ?:ebay_cached_transactions WHERE timestamp < ?i OR (timestamp < ?i AND status = ?s)', TIME - EBAY_TRANSACTION_EXPIRATION,  TIME - EBAY_TRANSACTION_EXECUTION_TIME, 'A');

    $site_ids = db_get_fields('SELECT DISTINCT site_id FROM ?:ebay_templates WHERE 1');
    if (!in_array(Ebay::instance()->site_id, $site_ids)) {
        $site_ids[] = Ebay::instance()->site_id;
    }

    foreach ($site_ids as $site_id) {
        if (!db_get_field('SELECT COUNT(transaction_id) FROM ?:ebay_cached_transactions WHERE type = ?s AND site_id = ?i AND timestamp > ?i', 'categories', $site_id, TIME - EBAY_CHECK_CATEGORIES_PERIOD)) {
            register_shutdown_function('fn_register_ebay_categories', $site_id);
        }
    }

    if (!db_get_field('SELECT COUNT(transaction_id) FROM ?:ebay_cached_transactions WHERE type = ?s AND timestamp > ?i', 'sites', TIME - EBAY_CHECK_SITES_PERIOD)) {
        register_shutdown_function('fn_register_ebay_sites');
    }

    foreach ($site_ids as $site_id) {
        if (!db_get_field('SELECT COUNT(transaction_id) FROM ?:ebay_cached_transactions WHERE type = ?s AND site_id = ?i AND timestamp > ?i', 'shippings', $site_id, TIME - EBAY_CHECK_SHIPPINGS_PERIOD)) {
            register_shutdown_function('fn_register_ebay_shippings', $site_id);
        }
    }

    return true;
}

function fn_register_ebay_categories($site_id = 0)
{
    $data = array(
        'timestamp' => TIME,
        'user_id' => $_SESSION['auth']['user_id'],
        'session_id' => Session::getId(),
        'status' => 'A',
        'type' => 'categories',
        'result' => '',
        'site_id' => $site_id
    );
    $transaction_id = db_query('INSERT INTO ?:ebay_cached_transactions ?e', $data);

    list(, $category_version) = Ebay::instance()->GetCategoryVersion();
    if ($category_version != db_get_field('SELECT result FROM ?:ebay_cached_transactions WHERE type = ?s AND site_id = ?i AND status = ?s ORDER BY transaction_id DESC LIMIT 1', 'categories', $site_id, 'C')) {

        list(, $categories) = Ebay::instance()->GetCategories('ReturnAll', '', 0);

        if (!empty($categories)) {
            db_query('DELETE FROM ?:ebay_categories WHERE site_id = ?i', $site_id);

            $data = array();
            foreach ($categories as $k => $v) {
                $data[] = array(
                    'category_id' => $v['CategoryID'],
                    'parent_id' => ($v['CategoryParentID'] != $v['CategoryID']) ? $v['CategoryParentID'] : 0,
                    'leaf' => (isset ($v['LeafCategory']) && $v['LeafCategory']) ? 'Y' : 'N',
                    'name' => $v['CategoryName'],
                    'level' => $v['CategoryLevel'],
                    'id_path' => fn_get_ebay_category_path($v['CategoryID'], $categories),
                    'full_name' => fn_get_ebay_category_full_name($v['CategoryID'], $categories),
                    'site_id' => $site_id
                );
                if (count($data) > 30) {
                    db_query('INSERT INTO ?:ebay_categories ?m', $data);
                    $data = array();
                }
            }
            if (!empty($data)) {
                db_query('INSERT INTO ?:ebay_categories ?m', $data);
                $data = array();
            }
            $data = array(
                'status' => 'C',
                'result' => $category_version
            );
            db_query('UPDATE ?:ebay_cached_transactions SET ?u WHERE transaction_id = ?i', $data, $transaction_id);
        }
    } else {
        $data = array(
            'status' => 'C',
            'result' => $category_version
        );
        db_query('UPDATE ?:ebay_cached_transactions SET ?u WHERE transaction_id = ?i', $data, $transaction_id);

    }

    return true;
}

function fn_get_ebay_category_path($category_id, $categories)
{
    static $paths = array();

    if ($categories[$category_id]['CategoryParentID'] == $category_id) {
        return $category_id;
    }

    return fn_get_ebay_category_path($categories[$category_id]['CategoryParentID'], $categories) . ',' . $category_id;
}

function fn_get_ebay_category_full_name($category_id, $categories)
{
    static $paths = array();

    if ($categories[$category_id]['CategoryParentID'] == $category_id) {
        return $categories[$category_id]['CategoryName'];
    }

    return fn_get_ebay_category_full_name($categories[$category_id]['CategoryParentID'], $categories) . ' > ' . $categories[$category_id]['CategoryName'];
}

function fn_get_ebay_shippings($service_type, $international = false)
{
    $site_id = Ebay::instance()->site_id;

    $condition = db_quote('site_id = ?i AND FIND_IN_SET(?s, service_type)', $site_id, $service_type);

    $condition .= db_quote(' AND is_international = ?s', ($international == true) ? 'Y' : 'N');

    $shippings = db_get_hash_multi_array("SELECT * FROM ?:ebay_shippings WHERE $condition ORDER BY name ASC", array('category', 'service_id'));

    if (empty($shippings)) {
        fn_set_notification('W', __('warning'), __('wait_for_shippings_cached'));
        register_shutdown_function('fn_register_ebay_shippings', $site_id);
    }

    return $shippings;
}

function fn_get_ebay_categories($parent_id = 0, $get_tree = false)
{
    $site_id = Ebay::instance()->site_id;

    if ($get_tree) {
        $ebay_categories = db_get_hash_array('SELECT * FROM ?:ebay_categories WHERE site_id = ?i AND FIND_IN_SET(?i, id_path) AND leaf = ?s ORDER BY full_name ASC', 'category_id', $site_id, $parent_id, 'Y');
    } else {
        $ebay_categories = db_get_hash_array('SELECT * FROM ?:ebay_categories WHERE site_id = ?i AND  parent_id = ?i ORDER BY name ASC', 'category_id', $site_id, $parent_id);
    }

    if (empty($ebay_categories)) {
        fn_set_notification('W', __('warning'), __('wait_for_categories_cached'));
        register_shutdown_function('fn_register_ebay_categories', $site_id);
    }

    return $ebay_categories;
}

function fn_get_ebay_sites()
{
    $sites = db_get_hash_single_array('SELECT * FROM ?:ebay_sites WHERE 1', array('site_id', 'site'));

    return $sites;
}

function fn_ebay_ult_check_store_permission($params, &$object_type, &$object_name, &$table, &$key, &$key_id)
{
    $controller = Registry::get('runtime.controller');
    if ($controller == 'ebay' && !empty($params['template_id'])) {
        $key = 'template_id';
        $key_id = $params[$key];
        $table = 'ebay_templates';
        $object_name = '#' . $key_id;
        $object_type = __('ebay');
    }

    return true;
}

function fn_delete_ebay_template($template_id)
{
    $template_company_id = db_get_field("SELECT company_id FROM ?:ebay_templates WHERE template_id = ?i", $template_id);
    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.simple_ultimate') && Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $template_company_id) {
        fn_set_notification('W', __('warning'), __('ebay_cant_remove_template'));

        return false;
    }
    db_query('UPDATE ?:products SET ebay_template_id = ?i WHERE ebay_template_id = ?i', 0, $template_id);
    db_query('DELETE FROM ?:ebay_templates WHERE template_id = ?i', $template_id);
    db_query('DELETE FROM ?:ebay_template_descriptions WHERE template_id = ?i', $template_id);

    return true;
}

function fn_update_ebay_template($data, $template_id = 0, $lang_code = CART_LANGUAGE)
{
    if (empty($data['name'])) {
        return false;
    }

    unset($data['template_id']);
    if (fn_allowed_for('ULTIMATE')) {
        // check that template owner was not changed by store administrator
        if (Registry::get('runtime.company_id') || empty($data['company_id'])) {
            $template_company_id = db_get_field('SELECT company_id FROM ?:ebay_templates WHERE template_id = ?i', $template_id);
            if (!empty($template_company_id)) {
                $data['company_id'] = $template_company_id;
            } else {
                if (Registry::get('runtime.company_id')) {
                    $template_company_id = $data['company_id'] = Registry::get('runtime.company_id');
                } else {
                    $template_company_id = $data['company_id'] = fn_get_default_company_id();
                }
            }
        } else {
            $template_company_id = $data['company_id'];
        }
    } else {
        if (Registry::get('runtime.company_id')) {
            $template_company_id = Registry::get('runtime.company_id');
        } else {
            $template_company_id = $data['company_id'];
        }
    }

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && !empty($template_company_id) && Registry::get('runtime.company_id') != $template_company_id) {
        $create = false;
    } else {

        if (isset($data['payment_methods']) && is_array($data['payment_methods'])) {
            $data['payment_methods'] = implode(',', $data['payment_methods']);
        }

        if (empty($data['root_sec_category'])) {
            $data['sec_category'] = '';
        }
        if (!empty($template_id)) {
            db_query('UPDATE ?:ebay_templates SET ?u WHERE template_id = ?i', $data, $template_id);

            db_query('UPDATE ?:ebay_template_descriptions SET ?u WHERE template_id = ?i AND lang_code = ?s', $data, $template_id, $lang_code);
            if (isset($_REQUEST['share_objects']) && isset($_REQUEST['share_objects']['ebay_templates']) && isset($_REQUEST['share_objects']['ebay_templates'][$template_id])) {
                $_products = db_get_fields("SELECT product_id FROM ?:products WHERE company_id NOT IN (?a) AND ebay_template_id = ?i", $_REQUEST['share_objects']['ebay_templates'][$template_id], $template_id);
                if (!empty($_products)) {
                    db_query("UPDATE ?:products SET ebay_template_id = 0 WHERE product_id IN (?a)", $_products);
                }
            }
        } else {
            $data['template_id'] = $template_id = db_query("INSERT INTO ?:ebay_templates ?e", $data);

            if (isset($data['name']) && empty($data['name'])) {
                unset($data['name']);
            }

            if (!empty($data['name'])) {

                foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
                    db_query("INSERT INTO ?:ebay_template_descriptions ?e", $data);
                }
            }
        }

        if ($data['use_as_default'] == 'Y') {
            db_query('UPDATE ?:ebay_templates SET use_as_default = ?s WHERE company_id = ?i AND NOT template_id = ?i', 'N', $template_company_id, $template_id);
        }
    }

    return $template_id;
}

function fn_get_ebay_template($template_id, $lang_code = CART_LANGUAGE)
{
    $avail_cond = '';

    $template_data = db_get_row('SELECT ?:ebay_templates.*, ?:ebay_template_descriptions.name, ?:ebay_sites.site FROM ?:ebay_templates LEFT JOIN ?:ebay_template_descriptions ON ?:ebay_templates.template_id = ?:ebay_template_descriptions.template_id AND ?:ebay_template_descriptions.lang_code = ?s LEFT JOIN ?:ebay_sites ON ?:ebay_templates.site_id = ?:ebay_sites.site_id WHERE ?:ebay_templates.template_id = ?i ?p', $lang_code, $template_id, $avail_cond);
    if (isset($template_data['payment_methods'])) {
        $template_data['payment_methods'] = explode(',', $template_data['payment_methods']);
    }

    return $template_data;
}

function fn_get_ebay_category_features($category_id)
{
    $features_list = array(
        'PayPalRequired',
        'VariationsEnabled',
        'MinimumReservePrice',
        'ReturnPolicyEnabled',
        'PaymentMethods',
        'StoreInventoryEnabled',
        'ListingDurations',
        'ConditionEnabled',
        'ConditionValues',
        'HandlingTimeEnabled'
    );

    list($trans_id, $result) = Ebay::instance()->GetCategoryFeatures($category_id, $features_list);

    return $result;
}

function fn_get_ebay_templates($params, $items_per_page = 0, $lang_code = CART_LANGUAGE, $get_simple = false)
{
    // Init filter
    $params = LastView::instance()->update('ebay_templates', $params);

    $fields = array(
        'templates.template_id',
        'templates.status',
        'descr.name',
        'templates.company_id'
    );

    // Define sort fields
    $sortings = array (
        'status' => 'templates.status',
        'name' => 'descr.name',
    );

    $condition = ''; //fn_get_company_condition('templates.company_id')
    $join = db_quote('LEFT JOIN ?:ebay_template_descriptions as descr ON templates.template_id = descr.template_id AND descr.lang_code = ?s', $lang_code);

    if (!empty($params['product_id'])) {
        if (fn_allowed_for('ULTIMATE')) {
            if (Registry::get('runtime.simple_ultimate')) {
                $condition = '';
            } else {
                $company_ids = fn_ult_get_shared_product_companies($params['product_id']);
                $tempalte_ids = db_get_fields("SELECT share_object_id FROM ?:ult_objects_sharing WHERE share_object_type = 'ebay_templates' AND share_company_id IN (?n)", $company_ids);
                $condition = db_quote(' AND templates.template_id IN (?n)', $tempalte_ids);
            }
        } elseif (fn_allowed_for('MULTIVENDOR')) {
            if (Registry::get('runtime.company_id')) {
                $condition = fn_get_company_condition('templates.company_id');
            } else {
                $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $params['product_id']);
                $condition = db_quote(" AND templates.company_id = ?i", $company_id);
            }
        }
    } else {
        if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.simple_ultimate') && Registry::get('runtime.company_id')) {
            $join .= db_quote(" INNER JOIN ?:ult_objects_sharing ON (?:ult_objects_sharing.share_object_id = templates.template_id AND ?:ult_objects_sharing.share_company_id = ?i AND ?:ult_objects_sharing.share_object_type = 'ebay_templates')", Registry::get('runtime.company_id'));
        }
    }

    $limit = '';
    $group_by = 'templates.template_id';

    // -- SORTINGS --
    if (empty($params['sort_by']) || empty($sortings[$params['sort_by']])) {
        $params['sort_by'] = 'name';
    }

    if (empty($params['sort_order'])) {
        $params['sort_order'] = 'asc';
    }

    $sorting = db_sort($params, $sortings);

    if (!empty($params['limit'])) {
        $limit = db_quote(" LIMIT 0, ?i", $params['limit']);

    } elseif (!empty($params['items_per_page'])) {
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    Registry::set('runtime.skip_sharing_selection', true);
    $templates = db_get_array("SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $fields) . " FROM ?:ebay_templates as templates $join WHERE 1 $condition GROUP BY $group_by $sorting $limit");
    Registry::set('runtime.skip_sharing_selection', false);

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = !empty($total)? $total : db_get_found_rows();
    } else {
        $params['total_items'] = count($templates);
    }

    if ($get_simple == true) {
        $_templates = array();
        foreach ($templates as $template) {
            $_templates[$template['template_id']] = $template['name'];
        }

        return $_templates;
    }

    return array($templates, $params);
}

function fn_add_ebay_logs()
{
    $setting = Settings::instance()->getSettingDataByName('log_type_ebay_requests');

    if (!$setting) {
        $setting = array(
            'name' => 'log_type_ebay_requests',
            'section_id' => 12, // Logging
            'section_tab_id' => 0,
            'type' => 'N',
            'position' => 10,
            'is_global' => 'N',
            'edition_type' => 'ROOT,VENDOR',
            'value' => '#M#all'
        );

        foreach (fn_get_translation_languages() as $lang_code => $_lang) {
            $descriptions[] = array(
                'object_type' => Settings::SETTING_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => __('ebay_requests')
            );
        }

        $setting_id = Settings::instance()->update($setting, null, $descriptions, true);
        $variant_id = Settings::instance()->updateVariant(array(
            'object_id'  => $setting_id,
            'name'       => 'all',
            'position'   => 5,
        ));
        foreach (fn_get_translation_languages() as $lang_code => $_lang) {
            $description = array(
                'object_id' => $variant_id,
                'object_type' => Settings::VARIANT_DESCRIPTION,
                'lang_code' => $lang_code,
                'value' => __('all')
            );
            Settings::instance()->updateDescription($description);
        }
    }

    return true;
}

function fn_ebay_save_log($type, $action, $data, $user_id, &$content, $event_type)
{
    if ($type == 'ebay_requests') {
        $errors = array();

        if (!empty($data['errors'])) {
            foreach ($data['errors'] as $k => $v) {
                $errors[] = __('error_code') . '(' . $v['ErrorCode'] . '): ' . $v['LongMessage'];
            }
        }

        $content = array (
            'method' => $data['method'] . ' (' . fn_explain_ebay_method($data['method']) . ')',
            'status' => $data['status'],
            'errors' => implode("\n\n", $errors)
        );
    }

    return true;
}

function fn_explain_ebay_method($method)
{
    $msg = array(
        'GetOrders' => __('ebay_method_get_orders'),
        'AddItems' => __('ebay_method_add_items'),
        'GetCategoryFeatures' => __('ebay_method_get_category_features'),
        'GetEbayDetails' => __('ebay_method_get_ebay_details'),
        'GetCategories' => __('ebay_method_get_categories'),
        'GetCategoryVersion' => __('ebay_method_get_category_version')
    );

    return $msg[$method];
}

function fn_ebay_calculate_item_hash($product = array())
{
    $hash = '';
    if (!empty($product['price'])) {
        if ($product['override'] == "Y") {
            $title = substr(strip_tags($product['ebay_title']), 0, 80);
            $description = !empty($product['ebay_description']) ? $product['ebay_description'] : $product['full_description'];
        } else {
            $title = substr(strip_tags($product['product']), 0, 80);
            $description = $product['full_description'];
        }
        $hash_data = array(
            'price' => fn_format_price($product['price']),
            'title' => $title,
            'description' => $description,
        );
        if (!empty($product['product_features'])) {
            $hash_data['product_features'] = serialize($product['product_features']);
        }
        $hash = fn_crc32(implode('_', $hash_data));
    }

    return $hash;
}

function fn_export_ebay_products($template, $product_ids, $auth)
{
    $parts = floor(count($product_ids) / 5) + 1;

    fn_set_progress('parts', $parts);

    $data = array();
    $i = 1;
    $j = 0;
    $success = true;

    foreach ($product_ids as $product_id) {
        fn_echo(' .');

        $data[$product_id] = fn_get_product_data($product_id, $auth, CART_LANGUAGE);
        fn_gather_additional_product_data($data[$product_id], true, true);
        $data[$product_id]['ebay_item_id'] = db_get_field('SELECT ebay_item_id FROM ?:ebay_template_products WHERE product_id = ?i AND template_id = ?i', $product_id, $template['template_id']);

        if ($data[$product_id]['ebay_item_id']) {
            fn_set_progress('echo', '<br />' . __('exporting_images_to_ebay'));
            $images_data = Ebay::instance()->UploadImages(array($data[$product_id]));
            list($transaction_id, $result, $error_code) = Ebay::instance()->ReviseItem($data[$product_id], $template, $images_data);
            if (!empty($result)) {
                if (!$error_code) {
                    $_data = array(
                        'ebay_item_id' => $data[$product_id]['ebay_item_id'],
                        'template_id' => $template['template_id'],
                        'product_id' => $product_id,
                        'product_hash' => fn_ebay_calculate_item_hash($data[$product_id])
                    );
                    db_query('REPLACE INTO ?:ebay_template_products ?e', $_data);
                } elseif ($error_code == 291) {
                    //listing time is over, we should relist item.
                    list($transaction_id, $result, $error_code) = Ebay::instance()->RelistItem($data[$product_id], $template, $images_data);
                    if (!$error_code) {
                        //Since the RelistItem return new ItemId we should remove old data.
                        db_query("DELETE FROM ?:ebay_template_products WHERE ebay_item_id = ?i", $data[$product_id]['ebay_item_id']);
                        $_data = array(
                            'ebay_item_id' => (int) $result->ItemID,
                            'template_id' => $template['template_id'],
                            'product_id' => $product_id,
                            'product_hash' => fn_ebay_calculate_item_hash($data[$product_id])
                        );
                        db_query('REPLACE INTO ?:ebay_template_products ?e', $_data);
                    } else {
                        $success = false;
                    }
                } else {
                    $success = false;
                }
            }

            unset($data[$product_id]);
            continue;
        }
    }

    if (!empty($data)) {
        fn_set_progress('echo', '<br />' . __('exporting_images_to_ebay'));
        $images_data = Ebay::instance()->UploadImages($data);
        fn_set_progress('echo', '<br />' . __('exporting_products_to_ebay'));
        $_data = array_chunk($data, 5);
        foreach ($_data as $products_data) {
            list($transaction_id, $result) = Ebay::instance()->AddItems($products_data, $template, $images_data);
            if (!empty($result)) {
                foreach ($result as $item_key => $item) {
                    $_data = array(
                        'ebay_item_id' => $item['ItemID'],
                        'template_id' => $template['template_id'],
                        'product_id' => $products_data[$item_key]['product_id'],
                        'product_hash' => $item['product_hash']
                    );
                    db_query('REPLACE INTO ?:ebay_template_products ?e', $_data);
                }
            } else {
                $success = false;
            }
        }
    }
    if ($success) {
        fn_set_notification('N', __('successful'), __('ebay_success_products_notice'));
    }

    return $success;
}

function fn_ebay_get_product_fields(&$fields)
{
    $fields[] = array(
        'name' => '[data][ebay_template_id]',
        'text' => __('ebay_template')
    );
}

function fn_prepare_xml_product_features($features)
{
    $product_features = '';
    if (!empty($features) && is_array($features)) {
        foreach ($features as $k => $feature) {
            $value = '';
            if ($feature['feature_type'] == 'G' && !empty($feature['subfeatures'])) {
                $product_features .= fn_prepare_xml_product_features($feature['subfeatures']);
                continue;
            } else {
                if ($feature['feature_type'] == 'C' && $feature['value'] == 'Y') {
                    $value = __('yes');
                } elseif ($feature['feature_type'] == 'D') {
                    $value = strftime(Settings::instance()->getValue('date_format', 'Appearance'), $feature['value_int']);
                } elseif ($feature['feature_type'] == 'M' && $feature['variants']) {
                    foreach ($feature['variants'] as $var) {
                        if ($var['selected']) {
                            $variants[] = $var['variant'];
                        }
                    }
                    $value = implode("</Value>\n<Value>", $variants);
                } elseif ($feature['feature_type'] == 'S' || $feature['feature_type'] == 'E') {
                    foreach ($feature['variants'] as $var) {
                        if ($var['selected']) {
                            $value = $var['variant'];
                        }
                    }
                } elseif ($feature['feature_type'] == 'N' || $feature['feature_type'] == 'O') {
                    $value = floatval($feature['value_int']);
                } else {
                    $value = htmlentities($feature['value']);
                }

                $product_features .= <<<EOT

                    <NameValueList>
                        <Name>$feature[description]</Name>
                        <Value>$value</Value>
                    </NameValueList>

EOT;
            }
        }
    }

    return $product_features;
}

function fn_get_ebay_orders()
{
    $success_orders = $failed_orders = array();
    setlocale(LC_TIME, 'en_US');

    $params = array(
        'OrderStatus' => 'Completed'
    );
    $last_transaction = db_get_field('SELECT timestamp FROM ?:ebay_cached_transactions WHERE type = ?s AND status = ?s ORDER BY timestamp DESC', 'orders', 'C'); // Need user_id

    if (!empty($last_transaction)) {
        $params['CreateTimeFrom'] = gmstrftime("%Y-%m-%dT%H:%M:%S", $last_transaction);
        $params['CreateTimeTo'] = gmstrftime("%Y-%m-%dT%H:%M:%S", TIME);
    }

    $data = array(
        'timestamp' => TIME,
        'user_id' => $_SESSION['auth']['user_id'],
        'session_id' => Session::getId(),
        'status' => 'A',
        'type' => 'orders',
        'result' => '',
        'site_id' => 0
    );
    $transaction_id = db_query('INSERT INTO ?:ebay_cached_transactions ?e', $data);

    list(,$ebay_orders) = Ebay::instance()->GetOrders($params);
    $data = array(
        'status' => 'C',
        'result' => count($ebay_orders)
    );
    db_query('UPDATE ?:ebay_cached_transactions SET ?u WHERE transaction_id = ?i', $data, $transaction_id);
    if (!empty($ebay_orders)) {
        foreach ($ebay_orders as $k => $v) {
            $item_transactions = $v['TransactionArray'];
            $cart = $products = array();

            if (!is_array($item_transactions)) {
                $item_transactions = array($item_transactions->Transaction);
            }
            $i = 1;

            foreach ($item_transactions as $item) {
                $email = (string) $item->Buyer->Email;
                break;
            }

            $shipping_address = $v['ShippingAddress'];
            $customer_name = explode(' ', (string) $shipping_address->Name);
            $firstname = array_shift($customer_name);
            $lastname = implode(' ', $customer_name);
            $cart = array(
                'user_id' => 0,
                'company_id' => Registry::get('runtime.company_id'),
                'email' => $email,
                'ebay_order_id' => $v['OrderID'],
                'status' => 'P',
                'timestamp' => strtotime($v['CreatedTime']),
                'payment_id' => 0,
                'user_data' => array(
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'phone' => (string) $shipping_address->Phone,

                    's_firstname' => $firstname,
                    's_lastname' => $lastname,
                    's_address' => (string) $shipping_address->Street1,
                    's_city' => (string) $shipping_address->CityName,
                    's_state' => (string) $shipping_address->StateOrProvince,
                    's_country' => (string) $shipping_address->Country,
                    's_phone' => (string) $shipping_address->Phone,
                    's_zipcode' => (string) $shipping_address->PostalCode,


                    'b_firstname' => $firstname,
                    'b_lastname' => $lastname,
                    'b_address' => (string) $shipping_address->Street1,
                    'b_city' => (string) $shipping_address->CityName,
                    'b_state' => (string) $shipping_address->StateOrProvince,
                    'b_country' => (string) $shipping_address->Country,
                    'b_phone' => (string) $shipping_address->Phone,
                    'b_zipcode' => (string) $shipping_address->PostalCode,

                ),
                'total' => $v['Total'],
                'subtotal' => $v['Subtotal'],
                'shipping_cost' => (float) $v['ShippingServiceSelected']->ShippingServiceCost
            );

            foreach ($item_transactions as $item) {
                $_item = (array) $item->Item;
                $product_id = db_get_field('SELECT product_id FROM ?:ebay_template_products WHERE ebay_item_id = ?i', $_item['ItemID']); // Need check company_id
                if (!$product_id) {
                    continue;
                }
                $product = fn_get_product_data($product_id, $cart['user_data']);

                $extra = array (
                        "product_options" => array()
                    );

                $options = db_get_array(
                'SELECT ?:product_options.option_id, ?:product_options_descriptions.option_name, ?:product_option_variants_descriptions.variant_id, ?:product_option_variants_descriptions.variant_name
                FROM ?:product_options
                JOIN ?:product_option_variants ON ?:product_option_variants.option_id = ?:product_options.option_id
                JOIN ?:product_options_descriptions ON ?:product_options_descriptions.option_id = ?:product_options.option_id
                JOIN ?:product_option_variants_descriptions ON ?:product_option_variants_descriptions.variant_id = ?:product_option_variants.variant_id
                WHERE product_id =?i', $product_id);
                if (isset($item->Variation)) {
                    $variations_xml = (array) $item->Variation->VariationSpecifics;
                    if (isset($variations_xml['NameValueList']->Name)) {
                        $variations = (array) $variations_xml['NameValueList'];
                    } else {
                        foreach ($variations_xml['NameValueList'] as $variation) {
                            $variations[] = (array) $variation;
                        }
                    }
                    if (isset($variations)) {
                        if (isset($variations['Name'])) {
                            foreach ($options as $option) {
                                if ($variations['Name'] == $option['option_name'] && $variations['Value'] == $option['variant_name']) {
                                    $extra['product_options'][$option['option_id']] = $option['variant_id'];
                                }
                            }
                        } else {
                            foreach ($variations as $variation) {
                                foreach ($options as $option) {
                                    if ($variation['Name'] == $option['option_name'] && $variation['Value'] == $option['variant_name']) {
                                        $extra['product_options'][$option['option_id']] = $option['variant_id'];
                                    }
                                }
                            }
                        }
                        $variations = array();
                    }
                }

                $products[$i] = array(
                    'product_id' => $product_id,
                    'amount' => (int) $item->QuantityPurchased,
                    'price' => (float) $item->TransactionPrice,
                    'base_price' => (float) $item->TransactionPrice,
                    'is_edp' => $product['is_edp'],
                    'edp_shipping' => $product['edp_shipping'],
                    'free_shipping' => $product['free_shipping'],
                    'stored_price' => 'Y',
                    'company_id' => Registry::get('runtime.company_id'),
                    'extra' => $extra
                );
                unset($product);

                $i += 1;
            }
            if (empty($products)) {
                continue;
            }

            $cart['products'] = $products;
            unset($products);
            $location = fn_get_customer_location($cart['user_data'], $cart);
            $cart['product_groups'] = Shippings::groupProductsList($cart['products'], $location);

            list($order_id, $status) = fn_update_order($cart);

            if (!empty($order_id)) {
                fn_change_order_status($order_id, 'P', $status, fn_get_notification_rules(array(), false));
                $success_orders[] = $order_id;
            } else {
                $failed_orders[] = $cart['ebay_order_id'];
            }
        }
    }

    return array($success_orders, $failed_orders);
}

function fn_get_ebay_registration_notice()
{
    return __('ebay_registration_notice');
}

function fn_ebay_get_orders($params, $fields, $sortings, &$condition, $join, $group)
{

    if (!empty($params['ebay_orders']) && $params['ebay_orders'] == 'Y') {
        $condition .= db_quote(" AND ?:orders.ebay_order_id <> ?s", '');
    }

    return true;
}

function fn_ebay_update_product_post($product_data, $product_id, $lang_code, $create)
{
    if (empty($product_id)) {
        return false;
    }

    $auth = $_SESSION['auth'];

    $_product_data = fn_get_product_data($product_id, $auth, CART_LANGUAGE);
    fn_gather_additional_product_data($_product_data, true, true);

    if ($_product_data['override'] == "Y") {
        $title = substr(strip_tags($_product_data['ebay_title']), 0, 80);
        $description = !empty($_product_data['ebay_description']) ? $_product_data['ebay_description'] : $_product_data['full_description'];
    } else {
        $title = substr(strip_tags($_product_data['product']), 0, 80);
        $description = $_product_data['full_description'];
    }

    $hash_data = array(
        'price' => fn_format_price($_product_data['price']),
        'title' => $title,
        'description' => $description,
        'product_features' => serialize($_product_data['product_features'])
    );

    $product_hash = fn_crc32(implode('_', $hash_data));

    db_query('UPDATE ?:products SET product_hash = ?s WHERE product_id = ?i', $product_hash, $product_id);

    return true;
}

function fn_prepare_xml_product_options($params, $product = array(), $images_data = array())
{
    $variant_values = '';
    $variations_specifics = '';
    $NameValueList = '';
    $variations = '';
    $name = '';
    $price = 0;
    $spec_values = '';
    $pictures = '';

    list($inventory, , $product_options,) = fn_get_product_options_inventory_ebay($params);
    foreach ($product_options as $specific) {
        $pictures .= <<<EOT
        <Pictures>
        <VariationSpecificName>$specific[option_name]</VariationSpecificName>
EOT;
        foreach ($specific['variants'] as $spec) {
            if (!empty($spec['image_pair']['icon']['http_image_path']) && !empty($images_data[md5($spec['image_pair']['icon']['http_image_path'])])) {
                $img_url = $images_data[md5($spec['image_pair']['icon']['http_image_path'])];
            } else {
                $img_url = $images_data[md5('no_image.png')];
            }
            $pictures .= <<<EOT
            <VariationSpecificPictureSet>
                <VariationSpecificValue>$spec[variant_name]</VariationSpecificValue>
                <PictureURL>$img_url</PictureURL>
            </VariationSpecificPictureSet>
EOT;
            $spec_values .= <<<EOT
            <Value>$spec[variant_name]</Value>\n
EOT;
        }
        $pictures .= <<<EOT
        </Pictures>
EOT;
        $NameValueList .= <<<EOT
            <NameValueList>
                <Name>$specific[option_name]</Name>
                $spec_values
            </NameValueList>
EOT;
        $spec_values = '';
    }

    foreach ($inventory as $combination) {
        foreach ($combination['combination'] as $option => $variant) {
        $option_names = $product_options[$option]['option_name'];
        $current_value = $product_options[$option]['variants'][$variant]['variant_name'];
        $variant_values .= <<<EOT
            <NameValueList>
                <Name>$option_names</Name>
                <Value>$current_value</Value>\n
            </NameValueList>

EOT;
        $name .= $option_names . $current_value;

            if ($product_options[$option]['variants'][$variant]['modifier_type'] == 'A') {
                if ($price == 0) {
                    $price = $product['base_price'] + ($product_options[$option]['variants'][$variant]['modifier']);
                } else {
                    $price = $price + ($product_options[$option]['variants'][$variant]['modifier']);
                }
            } else {
                if ($price == 0) {
                    $price = ($product['base_price'] + (($product['base_price'] * $product_options[$option]['variants'][$variant]['modifier'])/100));
                } else {
                    $price = ($price + (($price * $product_options[$option]['variants'][$variant]['modifier'])/100));
                }
            }
        }

        $track_with_options = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product['product_id']);
        if ($track_with_options == ProductTracking::TRACK_WITH_OPTIONS) {
            $quant = $combination['amount'];
        } else {
            $quant = $product['amount'];
        }

        $variations .= <<<EOT
        \n<Variation>
            <SKU>$name</SKU>
            <StartPrice>$price</StartPrice>
            <Quantity>$quant</Quantity>
            <VariationSpecifics>
            $variant_values
            </VariationSpecifics>
        </Variation>
EOT;
        $variant_values = '';
        $price = 0;
        $name = '';
    }

    $NameValueList = '<VariationSpecificsSet>' . $NameValueList . '</VariationSpecificsSet>' . $pictures;

    return $NameValueList.$variations;
}

function fn_get_product_options_inventory_ebay($params, $items_per_page = 0, $lang_code = DESCR_SL)
{
    $default_params = array (
        'page' => 1,
        'product_id' => 0,
        'items_per_page' => 0
    );

    $params = array_merge($default_params, $params);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:product_options_inventory WHERE product_id = ?i", $params['product_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $inventory = db_get_array("SELECT * FROM ?:product_options_inventory WHERE product_id = ?i ORDER BY position $limit", $params['product_id']);

    foreach ($inventory as $k => $v) {
        $inventory[$k]['combination'] = fn_get_product_options_by_combination($v['combination']);
        $inventory[$k]['image_pairs'] = fn_get_image_pairs($v['combination_hash'], 'product_option', 'M', true, true, $lang_code);
    }

    $product_options = fn_get_product_options($params['product_id'], $lang_code, true, true);
    $product_inventory = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $params['product_id']);

    return array($inventory, $params, $product_options, $product_inventory);
}

function fn_ebay_get_products($params, $fields, $sortings, &$condition, &$join, $sorting, $group_by, $lang_code, $having = array())
{
    if (!empty($params['ebay_update']) && $params['ebay_update'] == 'W') {
        $join .= db_quote(' LEFT JOIN ?:ebay_template_products ON ?:ebay_template_products.product_id = products.product_id');
        $condition .= db_quote(' AND ?:ebay_template_products.product_hash <> products.product_hash');
    }

    if (!empty($params['ebay_update']) && $params['ebay_update'] == 'P') {
        $join .= db_quote(' LEFT JOIN ?:ebay_template_products ebay_products ON ebay_products.product_id = products.product_id');
        $condition .= db_quote(' AND ebay_products.product_hash IS NOT NULL');
    }

    return true;
}

/**
 * Shows information popup about commercial using
 *
 * @return type
 */
function fn_add_ebay_commercial_info()
{
    fn_set_notification('I', __('ebay_commercial_using_title'), __('ebay_commercial_using_text'));
}

function fn_ebay_get_license_notice()
{
    $ebay_license_url = Registry::get('config.resources.product_url') . '/ebay-synchronization.html';
    return __('ebay_license_notice', array('[ebay_license_url]' => $ebay_license_url));
}

function fn_addon_dynamic_url_ebay($url, $delete_url)
{
    if (!fn_ebay_check_license(true, true)) {
        $url .= "&selected_section=ebay_license_info";
    }
    return array($url, $delete_url);
}
