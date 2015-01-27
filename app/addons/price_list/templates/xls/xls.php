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
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('ITEMS_PER_PAGE', 50);
define('MAX_SIZE', 50);
define('CATEGORY_NAME_HEIGHT', 20);

// Default document style
define('DEFAULT_FONT_SIZE', 10);
define('DEFAULT_FONT_FAMILY', 'Arial');
define('DEFAULT_HORIZONTAL_ALIGN', 'left');
define('DEFAULT_VERTICAL_ALIGN', 'top');

// Field heading definition
define('FIELD_HEADING_HEIGHT', 20);
define('FIELD_HEADING_BOLD', true);
define('FIELD_HEADING_FONT_SIZE', 8);
define('FIELD_HEADING_FONT_FAMILY', 'Arial');
define('FIELD_HEADING_HORIZONTAL_ALIGN', 'left');
define('FIELD_HEADING_VERTICAL_ALIGN', 'center');

// Category heading definition
define('CATEGORY_HEADING_BOLD', true);
define('CATEGORY_HEADING_FONT_SIZE', 10);
define('CATEGORY_HEADING_FONT_FAMILY', 'Arial');
define('CATEGORY_HEADING_HORIZONTAL_ALIGN', 'left');
define('CATEGORY_HEADING_VERTICAL_ALIGN', 'bottom');
define('CATEGORY_HEADING_FONT_COLOR', 'FFFFFF');
define('CATEGORY_HEADING_BG_COLOR', '808080');

// Simple field definition
define('FIELD_BOLD', 0);
define('FIELD_FONT_SIZE', 8);
define('FIELD_FONT_FAMILY', 'Arial');
define('FIELD_BOTTOM_BORDER', 'hair');
define('FIELD_BOTTOM_BORDER_COLOR', '000000');
define('FIELD_HORIZONTAL_ALIGN', 'left');
define('FIELD_VERTICAL_ALIGN', 'top');
define('FIELD_TEXT_WRAP', true);
define('FIELD_NUM_FORMAT', 0);
define('FIELD_BG_COLOR', 'C0C0C0');
define('FIELD_MWIDTH', 1.5);

// Image width and height convert from pixels
define('IMAGE_WIDTH_PERCENT', 0.15);
define('IMAGE_HEIGHT_PERCENT', 0.8);

// Show xml content
error_reporting(E_ERROR);
ini_set('display_errors', '1');

set_time_limit(0);

fn_price_list_timer(); // Start timer;

$filename = fn_get_cache_path() . 'price_list/price_list_' . CART_LANGUAGE . '.xlsx'; // Must be unique for each xls mode.

if (Storage::instance('statics')->isExist($filename)) {

    Storage::instance('statics')->get($filename);
    exit;

} elseif (!fn_price_list_is_xls_supported()) {

    $url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : (empty($_SERVER['HTTP_REFERER']) ? fn_url() : $_SERVER['HTTP_REFERER']);

    fn_redirect($url);

} else {

    // FIX ME manually unpacking options
    $selected_fields = Registry::get('addons.price_list.price_list_fields');
    if (strpos($selected_fields, '#M#') === 0) {
        parse_str(str_replace('#M#', '', $selected_fields), $selected_fields);
    }

    include_once(Registry::get('config.dir.addons') . "/price_list/lib/phpexcel/Classes/PHPExcel.php");
    include_once(Registry::get('config.dir.addons') . "/price_list/lib/phpexcel/Classes/PHPExcel/Writer/Excel2007.php");

    include_once(Registry::get('config.dir.addons') . '/price_list/core/class.counter.php');

    $styles = array (
        'default' => array(
            'font' => array (
                'size' => DEFAULT_FONT_SIZE,
                'name' => DEFAULT_FONT_FAMILY,

            ),
            'alignment' => array (
                'horizontal' => DEFAULT_HORIZONTAL_ALIGN,
                'vertical' => DEFAULT_VERTICAL_ALIGN,
            )
        ),
        'field_heading' => array(
            'font' => array (
                'bold' => FIELD_HEADING_BOLD,
                'size' => FIELD_HEADING_FONT_SIZE,
                'name' => FIELD_HEADING_FONT_FAMILY,

            ),
            'alignment' => array (
                'horizontal' => FIELD_HEADING_HORIZONTAL_ALIGN,
                'vertical' => FIELD_HEADING_VERTICAL_ALIGN,
            )
        ),
        'category_heading' => array(
            'font' => array (
                'bold' => CATEGORY_HEADING_BOLD,
                'size' => CATEGORY_HEADING_FONT_SIZE,
                'name' => CATEGORY_HEADING_FONT_FAMILY,
                'color' => array (
                    'rgb' => CATEGORY_HEADING_FONT_COLOR,
                ),
            ),
            'alignment' => array (
                'horizontal' => CATEGORY_HEADING_HORIZONTAL_ALIGN,
                'vertical' => CATEGORY_HEADING_VERTICAL_ALIGN,
            ),
            'fill' => array (
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array (
                    'rgb' => CATEGORY_HEADING_BG_COLOR
                )
            )
        ),
        'field_simple' => array(
            'font' => array (
                'bold' => FIELD_BOLD,
                'size' => FIELD_FONT_SIZE,
                'name' => FIELD_FONT_FAMILY,
            ),
            'alignment' => array (
                'horizontal' => FIELD_HORIZONTAL_ALIGN,
                'vertical' => FIELD_VERTICAL_ALIGN,
                'rotation' => 0,
                'wrap' => FIELD_TEXT_WRAP
            ),
            'borders' => array (
                'bottom' => array (
                    'style' => FIELD_BOTTOM_BORDER,
                    'color' => array (
                        'rgb' => FIELD_BOTTOM_BORDER_COLOR
                    )
                )
            ),
            'numberformat' => array (
                'code' => FIELD_NUM_FORMAT
            ),
        ),
        'field_simple_odd' => array(
            'font' => array (
                'bold' => FIELD_BOLD,
                'size' => FIELD_FONT_SIZE,
                'name' => FIELD_FONT_FAMILY,
            ),
            'alignment' => array (
                'horizontal' => FIELD_HORIZONTAL_ALIGN,
                'vertical' => FIELD_VERTICAL_ALIGN,
                'rotation' => 0,
                'wrap' => FIELD_TEXT_WRAP
            ),
            'borders' => array (
                'bottom' => array (
                    'style' => FIELD_BOTTOM_BORDER,
                    'color' => array (
                        'rgb' => FIELD_BOTTOM_BORDER_COLOR
                    )
                )
            ),
            'numberformat' => array (
                'code' => FIELD_NUM_FORMAT
            ),
            'fill' => array (
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array (
                    'rgb' => FIELD_BG_COLOR
                )
            )
        )
    );

    $width = array();

    $counter = new Counter(100, '.');

    $pexcel = new PHPExcel();
    $pexcel->getProperties()->setTitle(__('price_list'));
    $pexcel->getDefaultStyle()->applyFromArray($styles['default']);

    $pexcel->setActiveSheetIndex(0);
    $worksheet = $pexcel->getActiveSheet();
    $worksheet->setTitle(__('price_list'));

    fn_echo(__('generating_xls') . '<br />');

    $row = 1;
    $page = 1;
    // Prepare XLS data
    $width = array();
    if (Registry::get('addons.price_list.group_by_category') == "Y") {
        // Display products according to the categories names.

        // Group the products by categories
        // Prepare XLS data
        $categories = fn_get_plain_categories_tree(0, false);

        $end_col = 'A';
        for ($i = 1; $i < count($selected_fields); $i++) {
            $end_col++;
        }
        foreach ($categories as $category) {

            if ($category['product_count'] == 0) {
                continue;
            }

            fn_echo('<br />' . $category['category']);
            $counter->clear();

            // Write category name
            $col = 'A';
            $worksheet->getRowDimension($row)->setRowHeight(CATEGORY_NAME_HEIGHT);
            $worksheet->setCellValue($col . $row, fn_price_list_build_category_name($category['id_path']));
            $worksheet->mergeCells($col . $row . ':' . $end_col . $row);
            $worksheet->getStyle($col . $row)->applyFromArray($styles['category_heading']);

            $row++;

            // output category products
            $params = $_REQUEST;
            $params['sort_by'] = $price_schema['fields'][Registry::get('addons.price_list.price_list_sorting')]['sort_by'];
            $params['page'] = $page;
            $params['skip_view'] = 'Y';

            $params['cid'] = $category['category_id'];
            $params['subcats'] = 'N';

            fn_price_list_print_products($params, $worksheet, $counter, $row, $width, $selected_fields, $price_schema, $styles);
        }

    } else {

        $params = $_REQUEST;
        $params['sort_by'] = $price_schema['fields'][Registry::get('addons.price_list.price_list_sorting')]['sort_by'];
        $params['page'] = $page;
        $params['skip_view'] = 'Y';

        fn_price_list_print_products($params, $worksheet, $counter, $row, $width, $selected_fields, $price_schema, $styles);
    }

    foreach ($width as $col => $size) {
        if ($size > MAX_SIZE) {
            $size = MAX_SIZE;
        }

        $worksheet->getColumnDimension($col)->setWidth($size);
    }

    $writer = new PHPExcel_Writer_Excel2007($pexcel);

    $imp_filename = fn_create_temp_file();
    $writer->save($imp_filename);
    $pexcel->disconnectWorksheets();
    unset($pexcel);
    Storage::instance('statics')->put($filename, array(
        'file' => $imp_filename,
        'caching' => true
    ));

    fn_echo('<br />' . __('done'));
}

/**
 * Checks if server configuration supports xml creation
 *
 * @return bool False if some components are not installed, otherwise True
 */
function fn_price_list_is_xls_supported()
{
    $result = true;

    // check for ZipArchive class exists
    // phpexcel does not work without zip support
    if (!class_exists('ZipArchive')) {
        fn_echo(__('price_list_ziparchive_not_installed') . '<br />');
        $result = false;
    }

    return $result;
}

function fn_price_list_print_products($params, &$worksheet, &$counter, &$row, &$width, $selected_fields, $price_schema, $styles)
{
    $worksheet->getRowDimension($row)->setRowHeight(FIELD_HEADING_HEIGHT);

    $col = 'A';
    foreach ($selected_fields as $field => $active) {

        $worksheet->setCellValue($col . $row, $price_schema['fields'][$field]['title']);
        $worksheet->getStyle($col . $row)->applyFromArray($styles['field_heading']);
        if (!isset($width[$col]) || $width[$col] < strlen($price_schema['fields'][$field]['title'])) {
            $width[$col] = strlen($price_schema['fields'][$field]['title']);
        }

        $col++;
    }

    $row++;

    $total = ITEMS_PER_PAGE;
    $fill = true;

    while (ITEMS_PER_PAGE * ($params['page'] - 1) <= $total) {
        list($products, $search) = fn_get_products($params, ITEMS_PER_PAGE);
        $total = $search['total_items'];
        $params['page']++;

        $_params = array(
            'get_icon' => true,
            'get_detailed' => true,
            'get_options' => (Registry::get('addons.price_list.include_options') == 'Y')? true : false,
            'get_discounts' => false,
        );

        fn_gather_additional_products_data($products, $_params);

        // Write products information
        foreach ($products as $product) {
            if (Registry::get('addons.price_list.include_options') == 'Y' && $product['has_options']) {
                $product_comb = fn_price_list_get_combination($product);

                if (!empty($selected_fields['image'])) {
                    $default_image = $product['main_pair'];

                    $comb_hashes = db_get_hash_single_array("SELECT oi.combination, oi.combination_hash FROM ?:product_options_inventory AS oi LEFT JOIN ?:images_links as il ON oi.combination_hash = il.object_id AND object_type = ?s WHERE oi.product_id = ?i", array('combination', 'combination_hash'), 'product_option', $product['product_id']);

                    if (!empty($comb_hashes)) {
                        $default_image = fn_get_image_pairs($product['product_id'], 'product', 'M', true, false, CART_LANGUAGE);
                    }
                }

                foreach ($product_comb['combinations'] as $c_id => $c_value) {
                    if (!empty($selected_fields['image']) ) {
                        $combination = fn_get_options_combination($c_value);
                        if (!empty($comb_hashes[$combination])) {
                            $product['main_pair'] = fn_get_image_pairs($comb_hashes[$combination], 'product_option', 'M', true, true, CART_LANGUAGE);
                        } else {
                            $product['main_pair'] = $default_image;
                        }
                    }
                    $product['price'] = $product_comb['combination_prices'][$c_id];
                    $product['weight'] = $product_comb['combination_weight'][$c_id];
                    $product['amount'] = $product_comb['combination_amount'][$c_id];
                    $product['product_code'] = $product_comb['combination_code'][$c_id];

                    fn_price_list_print_product_data($product, $worksheet, $row, $width, $selected_fields, $styles, $c_value);
                    $row++;
                }
            } else {
                fn_price_list_print_product_data($product, $worksheet, $row, $width, $selected_fields, $styles);
                $row++;

            }
        }

        $counter->Out();
    }

    return true;
}

function fn_price_list_print_product_data($product, &$worksheet, $row, &$width, $selected_fields, $styles, $options_variants = array())
{
    $col = 'A';
    foreach ($selected_fields as $field => $active) {
        $worksheet->getStyle($col . $row)->applyFromArray($row % 2 == 0 ? $styles['field_simple_odd'] : $styles['field_simple']);

        if ($field == 'image') {

            $image_data = fn_image_to_display($product['main_pair'], Registry::get('settings.Thumbnails.product_lists_thumbnail_width'), Registry::get('settings.Thumbnails.product_lists_thumbnail_height'));

            if (!empty($image_data)) {

                $mime_type = fn_get_file_type($image_data['image_path']);
                $src = $image_data['absolute_path'];
                $image_width = $image_data['width'];
                $image_height = $image_data['height'];

                if ($mime_type == 'image/gif' && function_exists('imagecreatefromgif')) {
                    $img_res = imagecreatefromgif($src);
                } elseif ($mime_type == 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
                    $img_res = imagecreatefromjpeg($src);
                } elseif ($mime_type == 'image/png' && function_exists('imagecreatefrompng')) {
                    $img_res = imagecreatefrompng($src);
                } else {
                    $img_res = false;
                }

                if ($img_res) {
                    if (!isset($width[$col]) || $width[$col] < $image_width) {
                        $width[$col] = $image_width * IMAGE_WIDTH_PERCENT;
                    }

                    $img_descr = $image_data['alt'];
                    $drawing = new PHPExcel_Worksheet_MemoryDrawing();
                    $drawing->setName($img_descr);
                    $drawing->setDescription($img_descr);
                    $drawing->setImageResource($img_res);
                    $drawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
                    $drawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
                    $drawing->setHeight($image_height);
                    $drawing->setWorksheet($worksheet);
                    $worksheet->getRowDimension($row)->setRowHeight($image_height * IMAGE_HEIGHT_PERCENT);
                    $drawing->setCoordinates($col . $row);
                }
            }
        } else {
            if ($field == 'price') {
                $product[$field] = fn_format_price($product[$field], CART_PRIMARY_CURRENCY, null, false);
            }

            if (!isset($width[$col]) || $width[$col] < strlen($product[$field])) {
                $width[$col] = strlen($product[$field]);
            }

            if (!empty($options_variants) && $field == 'product') {
                $options = array();
                foreach ($options_variants as $option_id => $variant_id) {
                    $option = $product['product_options'][$option_id]['option_name'] . ': ' . $product['product_options'][$option_id]['variants'][$variant_id]['variant_name'];
                    $options[] = $option;
                    if ($width[$col] < strlen($option)) {
                        $width[$col] = strlen($options);
                    }
                }
                $options = implode("\n", $options);
                $worksheet->setCellValue($col . $row, $product['product'] . "\n" . $options);

            } elseif ($field == 'price') {
                $worksheet->getCell($col . $row)->setValueExplicit($product[$field], PHPExcel_Cell_DataType::TYPE_STRING);
            } else {
                $worksheet->setCellValue($col . $row, $product[$field]);

            }
        }

        $col++;
    }

    return true;
}
