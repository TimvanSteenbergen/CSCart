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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_data_feeds_company_condition($field)
{
    if (fn_allowed_for('ULTIMATE')) {
        return fn_get_company_condition($field);
    }

    return '';
}

function fn_data_feeds_get_data($params = array(), $lang_code = CART_LANGUAGE)
{
    $condition = '';

    if (!empty($params['datafeed_id'])) {
        $condition .= db_quote(' AND feed.datafeed_id = ?i', $params['datafeed_id']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND feed.status = ?s', $params['status']);
    }

    if (!empty($params['cron'])) {
        $condition .= db_quote(' AND (feed.export_location = ?s OR feed.export_location = ?s)', 'S', 'F');
    }

    $feeds = db_get_array(
        "SELECT feed.datafeed_id, feed.categories, feed.products, feed.fields, feed.export_location, feed.export_by_cron, "
        . "feed.ftp_url, feed.ftp_user, feed.ftp_pass, feed.file_name, feed.csv_delimiter, feed.export_options, feed.save_dir, "
        . "feed.status, feed.exclude_disabled_products, feed.enclosure, descr.datafeed_name FROM ?:data_feeds AS feed "
        . "LEFT JOIN ?:data_feed_descriptions AS descr ON (feed.datafeed_id = descr.datafeed_id) "
        . "WHERE descr.lang_code = ?s ?p",
        $lang_code, $condition . fn_get_data_feeds_company_condition('feed.company_id')
    );

    if (!empty($feeds)) {
        foreach ($feeds as &$feed) {
            $feed['fields'] = unserialize($feed['fields']);
            $feed['export_options'] = unserialize($feed['export_options']);

            if (!empty($params['available_fields'])) {
                foreach ($feed['fields'] as $field_id => $field) {
                    if (isset($field['avail'])) {
                        if ($field['avail'] != $params['available_fields']) {
                            unset($feed['fields'][$field_id]);
                        }
                    } else {
                        unset($feed['fields'][$field_id]);
                    }
                }
            }
        }
    }

    if (!empty($params['single'])) {
        if ($params['single']) {
            return array_pop($feeds);
        }
    }

    return $feeds;
}

function fn_data_feeds_export($datafeed_id, $options = array(), $pattern = '')
{
    static $pattern;

    if (empty($pattern)) {
        $pattern = fn_get_pattern_definition('products');
    }

    $params['datafeed_id'] = $datafeed_id;
    $params['single'] = true;
    $params['available_fields'] = 'Y';
    $params = array_merge($params, $options);

    $datafeed_data = fn_data_feeds_get_data($params, DESCR_SL);

    if (empty($pattern) || empty($params['datafeed_id'])) {
        fn_set_notification('E', __('error'), __('error_exim_no_data_exported'));

        return false;
    }

    if ($datafeed_data['exclude_disabled_products'] == 'Y') {
        $params['status'] = 'A';
    }

    if (empty($datafeed_data['products']) && empty($datafeed_data['categories'])) {
        $params['cid'] = 0;
        $params['subcats'] = 'Y';
        $params['skip_view'] = 'Y';
        $params['extend'] = array('categories');

        list($products, $search) = fn_get_products($params);

        $pids = array_map(create_function('$product', '$pid = $product["product_id"]; return $pid;'), $products);

    } else {
        $pids = array();

        if (!empty($datafeed_data['products'])) {
            $pids = explode(',', $datafeed_data['products']);
        }

        if (!empty($datafeed_data['categories'])) {
            $params['cid'] = explode(',', $datafeed_data['categories']);
            $params['subcats'] = 'Y';
            $params['skip_view'] = 'Y';
            $params['extend'] = array('categories');

            list($products, $search) = fn_get_products($params);

            $_pids = array_map(create_function('$product', '$pid = $product["product_id"]; return $pid;'), $products);

            $pids = array_merge($pids, $_pids);
            unset($_pids);
        }

        $pids = array_unique($pids);
    }

    if (empty($pids)) {
        fn_set_notification('E', __('error'), __('error_exim_no_data_exported'));
        return false;
    }

    $pattern['condition']['conditions']['product_id'] = $pids;
    $fields = array();

    if (!empty($datafeed_data['fields'])) {
        foreach ($datafeed_data['fields'] as $field) {
            $fields[$field['field']] = $field['export_field_name'];
        }
    }

    $features = db_get_array('SELECT feature_id, description FROM ?:product_features_descriptions WHERE lang_code = ?s', DESCR_SL);

    $features_fields = array();

    if (!empty($features)) {
        foreach ($features as $feature) {
            $features_fields[$feature['description']] = array(
                'process_get' => array ('fn_data_feeds_get_product_features', '#key', '#field', '#lang_code'),
                'linked' => false,
            );
        }
    }

    $pattern['export_fields'] = array_merge($pattern['export_fields'], $features_fields);

    $options = $datafeed_data['export_options'];
    $options['delimiter'] = $datafeed_data['csv_delimiter'];
    $options['filename'] = $datafeed_data['file_name'];
    $options['fields_names'] = true;
    $options['force_header'] = true;
    $pattern['enclosure'] = !empty($datafeed_data['enclosure']) ? $datafeed_data['enclosure'] : '';
    if (!empty($fields)) {
        if (fn_export($pattern, $fields, $options) == true) {
            $errors = false;

            $export_location = empty($params['location']) ? $datafeed_data['export_location'] : $params['location'];

            if ($export_location == 'S') {
                if (file_exists(fn_get_files_dir_path() . $datafeed_data['file_name']) && is_dir($datafeed_data['save_dir'])) {
                    fn_rename(fn_get_files_dir_path() . $datafeed_data['file_name'], $datafeed_data['save_dir'] . '/' . $datafeed_data['file_name']);
                } else {
                    $errors = true;

                    fn_set_notification('E', __('error'), __('check_server_export_settings'));
                }

            } elseif ($export_location == 'F') {
                if (empty($datafeed_data['ftp_url'])) {
                    $errors = true;

                    fn_set_notification('E', __('error'), __('ftp_connection_problem'));

                } else {
                    preg_match("/[^\/^\\^:]+/", $datafeed_data['ftp_url'], $matches);
                    $host = $matches[0];

                    preg_match("/.*:([0-9]+)/", $datafeed_data['ftp_url'], $matches);
                    $port = empty($matches[1]) ? 21 : $matches[1];

                    preg_match("/[^\/]+(.*)/", $datafeed_data['ftp_url'], $matches);
                    $url = empty($matches[1]) ? '' : $matches[1];

                    $conn_id = @ftp_connect($host, $port);
                    $result = @ftp_login($conn_id, $datafeed_data['ftp_user'], $datafeed_data['ftp_pass']);
                    if (!empty($url)) {
                        @ftp_chdir($conn_id, $url);
                    }

                    $filename = fn_get_files_dir_path() . $datafeed_data['file_name'];

                    if ($result) {
                        if (@ftp_put($conn_id, $datafeed_data['file_name'], $filename, FTP_ASCII)) {
                            unlink($filename);
                        } else {
                            $errors = true;

                            fn_set_notification('E', __('error'), __('ftp_connection_problem'));
                        }
                    } else {
                        $errors = true;

                        fn_set_notification('E', __('error'), __('ftp_connection_problem'));
                    }

                    @ftp_close($conn_id);
                }
            }

            if (!$errors) {
                fn_set_notification('N', __('notice'), __('text_exim_data_exported'));

                return true;

            } else {
                unlink(fn_get_files_dir_path() . $datafeed_data['file_name']);

                return false;
            }

        } else {
            fn_set_notification('E', __('error'), __('error_exim_no_data_exported'));

            return false;
        }

    } else {
        fn_set_notification('E', __('error'), __('error_exim_fields_not_selected'));

        return false;
    }
}

function fn_data_feeds_get_product_features($product_id, $field, $lang_code)
{
    $feature_id = db_get_field('SELECT feature_descr.feature_id FROM ?:product_features_descriptions AS feature_descr WHERE feature_descr.description = ?s AND lang_code = ?s', $field, $lang_code);

    $result = false;
    if (!empty($feature_id)) {
        $feature_values = db_get_array('SELECT var_descr.variant, feature_val.value, feature_val.value_int FROM ?:product_feature_variant_descriptions AS var_descr RIGHT JOIN ?:product_features_values AS feature_val ON (feature_val.variant_id = var_descr.variant_id) WHERE feature_val.feature_id = ?i AND feature_val.product_id = ?i AND feature_val.lang_code = ?s GROUP BY var_descr.variant_id', $feature_id, $product_id, $lang_code);
        $variants = array();

        foreach ($feature_values as $value) {
            if ($value['variant']) {
                $variants[] = $value['variant'];
            } elseif ($value['value']) {
                $variants[] = $value['value'];
            } else {
                $variants[] = ($value['value_int'] ? floatval($value['value_int']) : 0);
            }
        }

        if ($variants) {
            $result = implode(', ', $variants);
        }
    }

    return $result;
}

function fn_data_feeds_get_features_fields()
{
    $features = db_get_array('SELECT ?:product_features_descriptions.feature_id, ?:product_features_descriptions.description FROM ?:product_features_descriptions LEFT JOIN ?:product_features ON (?:product_features_descriptions.feature_id = ?:product_features.feature_id) WHERE ?:product_features.feature_type <> ?s AND lang_code = ?s', 'G', DESCR_SL);
    $features_fields = array();

    if (!empty($features)) {
        foreach ($features as $feature) {
            $features_fields[$feature['description']] = array(
                'process_get' => array ('fn_data_feeds_get_product_features', '#key', '#lang_code'),
                'linked' => false,
            );
        }
    }

    return $features_fields;
}

/**
 * Gets data feed title
 *
 * @param int $datafeed_id Data feed identifier
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return string Title
 */
function fn_get_data_feed_name($datafeed_id, $lang_code = CART_LANGUAGE)
{
    $datafeed_name = db_get_field("SELECT datafeed_name FROM ?:data_feed_descriptions WHERE datafeed_id = ?i AND lang_code = ?s", $datafeed_id, $lang_code);

    return $datafeed_name;
}

if (fn_allowed_for('ULTIMATE')) {
    function fn_data_feeds_ult_check_store_permission($params, &$object_type, &$object_name, &$table, &$key, &$key_id)
    {
        if (Registry::get('runtime.controller') == 'data_feeds' && !empty($params['datafeed_id'])) {
            $key = 'datafeed_id';
            $key_id = $params[$key];
            $table = 'data_feeds';
            $object_name = fn_get_data_feed_name($key_id, DESCR_SL);
            $object_type = __('data_feed');

        }
    }
}
