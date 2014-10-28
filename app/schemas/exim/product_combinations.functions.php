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

function fn_exim_get_product_combination($product_id, $combination, $set_delimiter, $lang_code = CART_LANGUAGE)
{
    $selected_options = fn_get_product_options_by_combination($combination);
    $options = fn_get_selected_product_options($product_id, $selected_options, $lang_code);

    $return = array();

    if (!empty($options)) {
        foreach ($options as $option) {
            if (isset($selected_options[$option['option_id']])) {
                $return[] = $option['option_name'] . ': ' . $option['variants'][$selected_options[$option['option_id']]]['variant_name'];
            }
        }
    }

    return implode($set_delimiter, $return);
}

function fn_exim_put_product_combination($product_id, $product_name, $combination_code, $combination, $amount, &$counter, $set_delimiter)
{
    $pair_delimiter = ':';

    $multi_lang = array_keys($combination);
    $main_lang = reset($multi_lang);

    if (!empty($combination)) {
        // Get product_id
        $object_id = 0;
        if (!empty($product_id)) {
            $object_exists = db_get_field('SELECT COUNT(*) FROM ?:products WHERE product_id = ?i', $product_id);
            if ($object_exists) {
                $object_id = $product_id;
            }
        }

        if (empty($object_id) && !empty($product_name)) {
            $object_id = db_get_field('SELECT product_id FROM ?:product_descriptions WHERE product = ?s AND lang_code = ?s', $product_name[$main_lang], $main_lang);
        }

        if (empty($object_id)) {
            $counter['S']++;

            return false;
        }

        $options = array();
        foreach ($multi_lang as $lang_code) {
            $_options = explode($set_delimiter, $combination[$lang_code]);

            foreach ($_options as $key => $value) {
                $options[$key][$lang_code] = $value;
            }
        }

        if (!empty($options)) {
            $_combination = array();

            foreach ($options as $option_pair) {
                $pair = explode($pair_delimiter, $option_pair[$main_lang]);
                if (is_array($pair)) {
                    array_walk($pair, 'fn_trim_helper');
                    $option_id = db_get_field("SELECT o.option_id FROM ?:product_options_descriptions as d INNER JOIN ?:product_options as o ON o.option_id = d.option_id AND o.product_id = ?i WHERE d.option_name = ?s AND d.lang_code = ?s LIMIT 1", $object_id, $pair[0], $main_lang);
                    if (empty($option_id)) {
                        // Search for the global product options
                        $option_id = db_get_field("SELECT o.option_id FROM ?:product_options_descriptions as d INNER JOIN ?:product_options as o ON o.option_id = d.option_id AND o.product_id = ?i WHERE d.option_name = ?s AND d.lang_code = ?s LIMIT 1", 0, $pair[0], $main_lang);
                    }
                    $variant_id = db_get_field("SELECT v.variant_id FROM ?:product_option_variants_descriptions as d INNER JOIN ?:product_option_variants as v ON v.variant_id = d.variant_id AND v.option_id = ?i WHERE d.variant_name = ?s AND d.lang_code = ?s LIMIT 1", $option_id, $pair[1], $main_lang);

                    if (empty($option_id) || empty($variant_id)) {
                        $counter['S']++;

                        return false;
                    }

                    $_combination[$option_id] = $variant_id;
                }
            }

            $combination = fn_get_options_combination($_combination);
            $combination_hash = fn_generate_cart_id($object_id, array('product_options' => $_combination));

            $object_details = db_get_row('SELECT COUNT(*) as count, amount FROM ?:product_options_inventory WHERE combination_hash = ?i AND product_id = ?i', $combination_hash, $object_id);
            $_data = array(
                'product_id' => $object_id,
                'product_code' => $combination_code,
                'combination_hash' => $combination_hash,
                'combination' => $combination,
                'amount' => $amount,
            );

            if ($object_details['count']) {
                if (($object_details['amount'] <= 0) && ($_data['amount'] > 0)) {
                    fn_send_product_notifications($object_id);
                }
                db_query('UPDATE ?:product_options_inventory SET ?u WHERE combination_hash = ?i', $_data, $combination_hash);
                fn_set_progress('echo', __('updating') . ' ' . __('product_combinations') . '...', false);

                $counter['E']++;

            } else {
                db_query('INSERT INTO ?:product_options_inventory ?e', $_data);
                fn_set_progress('echo', __('creating') . ' ' . __('product_combinations') . '...', false);

                $counter['N']++;
            }

            fn_set_progress('echo', '<b>' . $object_id . '</b>.<br />', false);

            return $combination;
        }
    }

    $counter['S']++;

    return false;
}

function fn_import_check_product_combination_company_id(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        if (empty($primary_object_id) && empty($object['product_id'])) {
            $processed_data['S']++;
            $skip_record = true;

            return false;
        }

        if (!empty($primary_object_id)) {
            list($field, $value) = each($primary_object_id);
            $company_id = db_get_field('SELECT company_id FROM ?:products WHERE ' . $field . ' = ?s', $value);
        } else {
            $company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $object['product_id']);
        }

        if ($company_id != Registry::get('runtime.company_id')) {
            $processed_data['S']++;
            $skip_record = true;
        }
    }
}
