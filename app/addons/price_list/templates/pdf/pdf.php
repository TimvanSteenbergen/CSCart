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

use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('ITEMS_PER_PAGE', 30);
define('GENERAL_FONT_FAMILY', 'freeserif');
define('GENERAL_FONT_SIZE', 10);
define('GENERAL_MARGIN_TOP', 10);
define('GENERAL_MARGIN_LEFT', 10);
define('GENERAL_MARGIN_RIGHT', 10);
define('FIELDS_HEADER_FONT_SIZE', 11);
define('FIELDS_ODD_BG_COLOR', '#EEEEEE');
define('IMAGE_HEIGHT', 50);
define('CATEGORY_HEADER_FONT_SIZE', 12);
define('CATEGORY_HEADER_FONT_COLOR', '#FFFFFF');
define('CATEGORY_HEADER_BG_COLOR', '#888888');
define('TABLE_CELLPADDING', 4);
define('TABLE_CELLSPACING', 0);

// Min column width in percent
$min_width = array(
    'product' => 50,
    'product_code' => 13,
    'image' => 10,
);

error_reporting(E_ERROR);
ini_set('display_errors', '1');

set_time_limit(0);

fn_price_list_timer(); // Start timer;

$filename = fn_get_cache_path() . 'price_list/price_list_' . CART_LANGUAGE . '.pdf'; // Must be unique for each pdf mode.

if (Storage::instance('statics')->isExist($filename)) {

    Storage::instance('statics')->get($filename);
    exit;

} else {

    include_once Registry::get('config.dir.addons') . '/price_list/core/class.counter.php';

    $counter = new Counter(100, '.');

    $selected_fields = Registry::get('addons.price_list.price_list_fields');
    $max_perc = 100;
    $field_count = count($selected_fields);

    // First step. Check for the min width.
    $perc = intval($max_perc / $field_count);

    foreach ($selected_fields as $field_name => $active) {
        if (isset($min_width[$field_name])) {
            if ($min_width[$field_name] > $perc) {
                $max_perc -= $min_width[$field_name];
                $field_count--;
            }
        }
    }

    // Second step. Set up the new width values.
    $perc = intval($max_perc / $field_count);

    foreach ($selected_fields as $field_name => $active) {
        if ($min_width[$field_name] < $perc) {
            $price_schema['fields'][$field_name]['min_width'] = $perc;
        } else {
            $price_schema['fields'][$field_name]['min_width'] = $min_width[$field_name];
        }
    }

    if (Registry::get('addons.price_list.group_by_category') != 'Y') {
        // Output full products list

        fn_echo(__('generating_pdf') . '<br />');

        $tbl = '';

        $tbl .= '<table border="0" cellpadding="' . TABLE_CELLPADDING . '" cellspacing="' . TABLE_CELLSPACING . '" width="100%">';
        $tbl .= '<tr>';

        foreach (Registry::get('addons.price_list.price_list_fields') as $field_name => $active) {
            $tbl .= '<td style="font-size: ' . FIELDS_HEADER_FONT_SIZE . '" width="' . $price_schema['fields'][$field_name]['min_width'] . '%"><strong>' . $price_schema['fields'][$field_name]['title'] . '</strong></td>';
        }

        $tbl .= '</tr>';
        $tbl .= '</table>';

        Pdf::batchAdd($tbl);
        $tbl = '';

        $fill = true;
        $counter->clear();

        $page = 1;
        $total = ITEMS_PER_PAGE;
        $fill = true;

        $params = $_REQUEST;
        $params['sort_by'] = $price_schema['fields'][Registry::get('addons.price_list.price_list_sorting')]['sort_by'];
        $params['page'] = $page;
        $params['skip_view'] = 'Y';

        while (ITEMS_PER_PAGE * ($params['page'] - 1) <= $total) {
            list($products, $search) = fn_get_products($params, ITEMS_PER_PAGE);
            $total = $search['total_items'];

            $_params = array(
                'get_icon' => true,
                'get_detailed' => false,
                'get_options' => (Registry::get('addons.price_list.include_options') == 'Y')? true : false,
                'get_discounts' => false,
            );
            fn_gather_additional_products_data($products, $_params);

            $params['page']++;
            $tbl = '<table border="0" cellpadding="' . TABLE_CELLPADDING . '" cellspacing="' . TABLE_CELLSPACING . '" width="100%">';

            // Write products information
            foreach ($products as $product) {

                if ($fill) {
                    $style = 'style="background-color: ' . FIELDS_ODD_BG_COLOR . '"';
                } else {
                    $style = '';
                }

                if (Registry::get('addons.price_list.include_options') == 'Y' && $product['has_options']) {
                    $product = fn_price_list_get_combination($product);

                    foreach ($product['combinations'] as $c_id => $c_value) {

                        $product['price'] = $product['combination_prices'][$c_id];
                        $product['weight'] = $product['combination_weight'][$c_id];
                        $product['amount'] = $product['combination_amount'][$c_id];
                        $product['product_code'] = $product['combination_code'][$c_id];

                        $tbl .= fn_price_list_print_product_data($product, $selected_fields, $style, $price_schema, $c_value);

                        $fill = !$fill;
                        if ($fill) {
                            $style = 'style="background-color: ' . FIELDS_ODD_BG_COLOR . '"';
                        } else {
                            $style = '';
                        }

                        $counter->out();
                    }

                } else {
                    $tbl .= fn_price_list_print_product_data($product, $selected_fields, $style, $price_schema);

                    $fill = !$fill;
                }

                $counter->out();
            }

            $tbl .= '</table>';

            $counter->out();

            Pdf::batchAdd($tbl);
        }

    } else {
        fn_echo(__('generating_pdf') . '<br />');

        // Group the products by categories
        // Prepare PDF data
        $categories = fn_get_plain_categories_tree(0, false);

        foreach ($categories as $category) {

            if ($category['product_count'] == 0) {
                continue;
            }

            fn_echo('<br />' . $category['category']);
            $counter->clear();
            // Write category name
            $tbl = '';
            $tbl .= '<table border="0" cellpadding="' . TABLE_CELLPADDING . '" cellspacing="' . TABLE_CELLSPACING . '" width="100%">';
            $tbl .= '<tr>';
            $tbl .= '<td align="left" style="background-color: ' . CATEGORY_HEADER_BG_COLOR . '; font-size: ' . CATEGORY_HEADER_FONT_SIZE . '; color: ' . CATEGORY_HEADER_FONT_COLOR . '" colspan="' . count(Registry::get('addons.price_list.price_list_fields')) . '"><strong>' . fn_price_list_build_category_name($category['id_path']) . '</strong></td>';
            $tbl .= '</tr>';
            $tbl .= '</table>';

            // Write product head fields
            $tbl .= '<table border="0" cellpadding="' . TABLE_CELLPADDING . '" cellspacing="' . TABLE_CELLSPACING . '" width="100%">';
            $tbl .= '<tr>';
            foreach (Registry::get('addons.price_list.price_list_fields') as $field_name => $active) {
                $tbl .= '<td style="font-size: ' . FIELDS_HEADER_FONT_SIZE . ';" width="' . $price_schema['fields'][$field_name]['min_width'] . '%"><strong>' . $price_schema['fields'][$field_name]['title'] . '</strong></td>';
            }
            $tbl .= '</tr>';
            $tbl .= '</table>';

            Pdf::batchAdd($tbl);

            $page = 1;
            $total = ITEMS_PER_PAGE;
            $fill = true;

            $params = $_REQUEST;
            $params['sort_by'] = $price_schema['fields'][Registry::get('addons.price_list.price_list_sorting')]['sort_by'];
            $params['page'] = $page;
            $params['skip_view'] = 'Y';

            $params['cid'] = $category['category_id'];
            $params['subcats'] = 'N';

            while (ITEMS_PER_PAGE * ($params['page'] - 1) <= $total) {
                list($products, $search) = fn_get_products($params, ITEMS_PER_PAGE);
                $total = $search['total_items'];

                $_params = array(
                    'get_icon' => true,
                    'get_detailed' => true,
                    'get_options' => (Registry::get('addons.price_list.include_options') == 'Y')? true : false,
                    'get_discounts' => false,
                );
                fn_gather_additional_products_data($products, $_params);

                $params['page']++;
                $tbl = '<table border="0" cellpadding="' . TABLE_CELLPADDING . '" cellspacing="' . TABLE_CELLSPACING . '" width="100%">';

                // Write products information
                foreach ($products as $product) {

                    if ($fill) {
                        $style = 'style="background-color: ' . FIELDS_ODD_BG_COLOR . '"';
                    } else {
                        $style = '';
                    }

                    if (Registry::get('addons.price_list.include_options') == 'Y' && $product['has_options']) {
                        $product = fn_price_list_get_combination($product);

                        foreach ($product['combinations'] as $c_id => $c_value) {

                            $product['price'] = $product['combination_prices'][$c_id];
                            $product['weight'] = $product['combination_weight'][$c_id];
                            $product['amount'] = $product['combination_amount'][$c_id];
                            $product['product_code'] = $product['combination_code'][$c_id];

                            $tbl .= fn_price_list_print_product_data($product, $selected_fields, $style, $price_schema, $c_value);

                            $fill = !$fill;
                            if ($fill) {
                                $style = 'style="background-color: ' . FIELDS_ODD_BG_COLOR . '"';
                            } else {
                                $style = '';
                            }

                            $counter->out();
                        }

                    } else {
                        $tbl .= fn_price_list_print_product_data($product, $selected_fields, $style, $price_schema);

                        $fill = !$fill;
                    }

                    $counter->out();
                }

                $tbl .= '</table>';

                $counter->out();

                Pdf::batchAdd($tbl);
            }
        }
    }

    //Close and output PDF document
    $temp_filename = fn_create_temp_file();
    $imp_filename = $temp_filename . '.pdf';
    fn_rename($temp_filename, $imp_filename);
    Pdf::batchRender($imp_filename, true);
    Storage::instance('statics')->put($filename, array(
        'file' => $imp_filename,
        'caching' => true
    ));

    fn_echo('<br />' . __('done'));
}

/**
 *
 * Adds product data in HTML format to price list table
 * @param array $product Product data
 * @param array $selected_fields Product fields that should be in price list
 * @param string $style Product row style (similar to the HTML style attribute, e.g.: style="background-color: #EEEEEE")
 * @param array $price_schema Price list columns scheme
 * @param array $options_variants Product options variants
 *
 * @return string Product data row in HTML format
 */
function fn_price_list_print_product_data($product, $selected_fields, $style, $price_schema, $options_variants = array())
{
    $tbl = '<tr>';
    foreach ($selected_fields as $field_name => $active) {
        $tbl .= '<td ' . $style . ' width="' . $price_schema['fields'][$field_name]['min_width'] . '%">';
        if ($field_name == 'image') {
            if ($image_data = fn_image_to_display($product['main_pair'], 0, IMAGE_HEIGHT)) {
                $tbl .= '<img src="' . $image_data['image_path'] . '" width= "' . $image_data['width'] . '" height="' . $image_data['height'] . '" align="bottom" />';
            }
        } elseif ($field_name == 'product' && !empty($options_variants)) {
            $options = array();

            foreach ($options_variants as $option_id => $variant_id) {
                $options[] = $product['product_options'][$option_id]['option_name'] . ': ' . $product['product_options'][$option_id]['variants'][$variant_id]['variant_name'];
            }

            $options = implode('<br />', $options);

            $tbl .= $product[$field_name] . '<br />' . $options;
        } elseif ($field_name == 'price') {
            $tbl .= fn_format_price($product[$field_name], CART_PRIMARY_CURRENCY, null, false);
        } else {
            $tbl .= $product[$field_name];
        }
        $tbl .= '</td>';
    }
    $tbl .= '</tr>';

    return $tbl;
}
