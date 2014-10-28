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

use Tygh\Settings;
use Tygh\Storage;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_watermarks_init_company_data(&$params, &$company_id, &$company_data)
{
    if (fn_allowed_for('ULTIMATE')) {
        if ($company_id) {
            fn_define('WATERMARK_IMAGE_ID', $company_id);
            fn_define('WATERMARKS_DIR_NAME', 'watermarked/' . $company_id . '/');
        } else {
            fn_define('WATERMARK_IMAGE_ID', 0);
            fn_define('WATERMARKS_DIR_NAME', 'watermarked/');
        }

    } else {
        fn_define('WATERMARK_IMAGE_ID', 1);
        fn_define('WATERMARKS_DIR_NAME', 'watermarked/');
    }
}

function fn_get_watermark_settings($company_id = null)
{
    static $cache;

    if (!isset($cache['settings_' . $company_id])) {
        $settings = Settings::instance()->getValue('watermark', '', $company_id);
        $settings = unserialize($settings);

        if (empty($settings)) {
            $settings = array();
        }

        if (!empty($settings['type']) && $settings['type'] == 'G') {
            if (!empty($company_id)) {
                $settings['image_pair'] = fn_get_image_pairs($company_id, 'watermark', 'M');
            } else {
                $settings['image_pair'] = fn_get_image_pairs(WATERMARK_IMAGE_ID, 'watermark', 'M');
            }
        }

        $cache['settings_' . $company_id] = $settings;
    }

    return $cache['settings_' . $company_id];
}

function fn_replace_rewrite_condition($file_name, $condition, $comment)
{
    if (!empty($condition)) {
        $condition = "\n" .
            "# $comment\n" .
            "<IfModule mod_rewrite.c>\n" .
            "RewriteEngine on\n".
            $condition .
            "</IfModule>\n" .
            "# /$comment";
    }

    $content = fn_get_contents($file_name);
    if ($content === false) {
        $content = '';
    } elseif (!empty($content)) {
        // remove old instructions
        $data = explode("\n", $content);
        $remove_start = false;
        foreach ($data as $k=> $line) {
            if (preg_match("/# $comment/", $line)) {
                $remove_start = true;
            }

            if ($remove_start) {
                unset($data[$k]);
            }

            if (preg_match("/# \/$comment/", $line)) {
                $remove_start = false;
            }
        }
        $content = implode("\n", $data);
    }

    $content .= $condition;

    return fn_put_contents($file_name, $content);
}

function fn_get_apply_watermark_options()
{
    $option_types = array (
        'icons' => array (
            'use_for_product_icons',
            'use_for_category_icons'
        ),
        'detailed' => array (
            'use_for_product_detailed',
            'use_for_category_detailed'
        ),
    );

    $res = array();
    foreach ($option_types as $type => $options) {
        $res[$type] = db_get_hash_single_array("SELECT name, object_id  FROM ?:settings_objects WHERE name IN (?a)", array('name', 'object_id'), $options);
    }

    return $res;
}

/**
 * Clear generated watermarks
 *
 * @param array $images_types Images types to be cleared, clear all if empty
 * @return boolean Always true
 */
function fn_delete_watermarks($images_types)
{
    $path_types = array(
        'icons' => array (
            'category',
            'product',
            'thumbnails'
        ),
        'detailed' => array (
            'detailed'
        )
    );

    $delete_paths = array();
    foreach ($path_types as $k => $v) {
        if (empty($images_types) || !empty($images_types[$k])) {
            $delete_paths = array_merge($delete_paths, $path_types[$k]);
        }
    }

    $wt_paths = array(WATERMARKS_DIR_NAME);

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        $wt_paths = array();
        $companies = fn_get_short_companies();
        foreach ($companies as $company_id => $name) {
            $wt_paths[] = 'watermarked/' . $company_id . '/';
        }
    }

    foreach ($delete_paths as $path) {
        foreach ($wt_paths as $wt_path) {
            Storage::instance('images')->deleteDir($wt_path . $path);
        }
    }

    fn_clear_cache();

    return true;
}

function fn_is_need_watermark($object_type, $is_detailed = true, $company_id = null)
{
    if ($object_type == 'watermark') {
        return false;
    }

    $result = fn_is_watermarks_enabled($company_id);

    if ($result == true) {
        if ($object_type == 'product_option' || $object_type == 'variant_image') {
            $object_type = 'product';
        }

        $image_type = $is_detailed ? 'detailed' : 'icons';
        $option = 'use_for_' . $object_type . '_' . $image_type;

        if (!empty($company_id)) {
            $result = Settings::instance()->getValue($option, 'watermarks', $company_id) == 'Y';
        } else {
            $result = Registry::get('addons.watermarks.' . $option) == 'Y';
        }
    }

    return $result;
}

function fn_watermarks_generate_thumbnail_file_pre(&$image_path, &$lazy)
{
    if ($lazy == true) {
        return true;
    }

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        $pattern = '/^(.*)' . preg_quote(WATERMARKS_DIR_NAME, '/') . '[0-9]+\/(.*)$/';
    } else {
        $pattern = '/^(.*)' . preg_quote(WATERMARKS_DIR_NAME, '/') . '(.*)$/';
    }

    if (preg_match($pattern, $image_path, $matches)) {
        $image_path = $matches[1] . $matches[2];
    }

    return true;
}

function fn_watermarks_update_company(&$company_data, &$company_id, &$lang_code, &$action)
{
    if ($action == 'add') {
        // Clone watermark images
        $clone_from = !empty($company_data['clone_from']) && $company_data['clone_from'] != 'all' ? $company_data['clone_from'] : null;

        if (!is_null($clone_from)) {
            if (!empty($company_id)) {
                $clone_to = $company_id;
                $image_pair = fn_get_image_pairs($clone_from, 'watermark', 'M');
            } else {
                $clone_to = WATERMARK_IMAGE_ID;
                $image_pair = fn_get_image_pairs(WATERMARK_IMAGE_ID, 'watermark', 'M');
            }

            if (!empty($image_pair)) {
                fn_clone_image_pairs($clone_to, $clone_from, 'watermark');
            }
        } else {
            // check if company options are valid
            $option_types = fn_get_apply_watermark_options();

            foreach ($option_types as $type => $options) {
                foreach ($options as $name => $option_id) {
                    $image_name = ($type == 'icons') ? 'icon' : 'detailed';

                    Settings::instance($company_id)->updateValueById($option_id, 'N', $company_id);
                }
            }
        }
    }
}

function fn_is_watermarks_enabled($company_id = null)
{
    $settings = fn_get_watermark_settings($company_id);
    $enabled = true;

    if (empty($settings) || ($settings['type'] == 'T' && empty($settings['text']))) {
        $enabled = false;
    } elseif ($settings['type'] == 'G' && empty($settings['image_pair'])) {
        $enabled = false;
    }

    return $enabled;
}

function fn_watermarks_generate_thumbnail_post(&$relative_path, &$lazy)
{
    $image_path_info = fn_pathinfo($relative_path);
    $image_name = $image_path_info['filename'];

    $company_id = null;

    $prefix = WATERMARKS_DIR_NAME;

    $key =  'wt_data_' . md5($image_name);

    $condition = array('images', 'images_links');

    if (fn_allowed_for('ULTIMATE')) {
        $condition[] = 'products';
        $condition[] = 'categories';
    }

    Registry::registerCache($key, $condition, Registry::cacheLevel('static'));

    if (Registry::isExist($key) == false) {
        $image_data = db_get_row("SELECT l.* FROM ?:images AS i, ?:images_links AS l WHERE image_path LIKE ?l AND (l.image_id = i.image_id OR detailed_id = i.image_id)", $image_name . '.%');

        if (empty($image_data)) {
            return true;
        }

        if (fn_allowed_for('ULTIMATE')) {
            $image_data['company_id'] = fn_wt_get_image_company_id($image_data);
        }

        Registry::set($key, $image_data);
    } else {
        $image_data = Registry::get($key);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : $image_data['company_id'];
    }

    if (!empty($image_data['object_type']) && fn_is_need_watermark($image_data['object_type'], $image_data['object_type'] == 'detailed', $company_id)) {

        if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
            $prefix = WATERMARKS_DIR_NAME . $company_id . '/';
        }

        if (!Storage::instance('images')->isExist($prefix . $relative_path)) {
            fn_watermark_create($relative_path, $prefix . $relative_path, false, $company_id);
        }

        $relative_path = $prefix . $relative_path;
    }

    return true;
}

function fn_wt_get_image_company_id($image_data)
{
    $image_data['object_type'] = (isset($image_data['object_type']))?$image_data['object_type']:'';
    if ($image_data['object_type'] == 'category') {
        $company_id = db_get_field("SELECT company_id FROM ?:categories WHERE category_id = ?i", $image_data['object_id']);
    } elseif ($image_data['object_type'] == 'product') {
        $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $image_data['object_id']);
    } elseif ($image_data['object_type'] == 'variant_image') {
        $company_id = db_get_field("SELECT company_id FROM ?:product_option_variants AS ov LEFT JOIN ?:product_options AS po ON ov.option_id = po.option_id WHERE ov.variant_id = ?i", $image_data['object_id']);
    } elseif ($image_data['object_type'] == 'product_option') {
        $company_id = db_get_field("SELECT company_id FROM ?:product_options_inventory AS pi LEFT JOIN ?:products AS p ON pi.product_id = p.product_id WHERE pi.combination_hash = ?i", $image_data['object_id']);
    } else {
        // take any company_id
        $company_id = db_get_field("SELECT company_id FROM ?:companies");
    }

    return $company_id;
}

function fn_watermarks_attach_absolute_image_paths(&$image_data, &$object_type, &$path, &$image_name)
{
    if (!empty($image_data['image_path'])) {
        $is_detailed = ($object_type == 'detailed') ? true : false;
        $company_id = null;

        if (empty($image_data['object_type'])) {
            $image_data['object_type'] = $object_type;
        }

        $prefix = WATERMARKS_DIR_NAME;
        if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
            $company_id = fn_wt_get_image_company_id($image_data);
            $prefix = WATERMARKS_DIR_NAME . $company_id . '/';
        }

        if (fn_is_need_watermark($image_data['object_type'], $is_detailed, $company_id)) {
            $image_data['http_image_path'] = Storage::instance('images')->getUrl($prefix . $path . '/' . $image_name, 'http');
            $image_data['absolute_path'] = Storage::instance('images')->getAbsolutePath($prefix . $path . '/' . $image_name);
            $image_data['image_path'] = Storage::instance('images')->getUrl($prefix . $path . '/' . $image_name);

            if (!Storage::instance('images')->isExist($prefix . $path . '/' . $image_name)) {
                fn_watermark_create($image_data['relative_path'], $prefix . $path . '/' . $image_name, $is_detailed, $company_id);
            }
        }
    }

    return true;
}

/**
 * Delete watermarked images before deleteing image pair
 *
 * @param int $image_id Image identifier
 * @param int $pair_id Pair identifier
 * @param string $object_type Object type
 * @param string $image_file Deleted image file
 * @return boolean Always true
 */
function fn_watermarks_delete_image(&$image_id, &$pair_id, &$object_type, &$image_file)
{
    $dir = WATERMARKS_DIR_NAME;
    if (fn_allowed_for('ULTIMATE')) {
        $dir = 'watermarked/*/';
    }

    fn_delete_image_thumbnails($image_file, $dir);

    return true;
}

function fn_watermarks_get_route(&$req, &$result, &$area, &$is_allowed_url)
{
    if (!empty($req['dispatch']) && $req['dispatch'] == 'watermark.create') {
        $is_allowed_url = true;
    }
}

function fn_watermark_create($original_image, $watermarked_image, $is_detailed = false, $company_id = null, $generate_watermark = true)
{
    $w_settings = fn_get_watermark_settings($company_id);

    if (empty($w_settings)) {
        return false;
    }

    $original_abs_path = Storage::instance('images')->getAbsolutePath($original_image);
    list($w_settings['horizontal_position'], $w_settings['vertical_position']) = explode('_', $w_settings['position']);
    list($original_width, $original_height, $original_mime_type) = fn_get_image_size($original_abs_path);

    if (empty($original_width) || empty($original_height)) {
        return false;
    }

    if (!$image = fn_create_image_from_file($original_abs_path, $original_mime_type)) {
        return false;
    }

    if (!$generate_watermark) {
        Storage::instance('images')->put($watermarked_image, array(
            'file' => $original_abs_path,
            'keep_origins' => true
        ));

        return true;
    }

    $dest_x = $dest_y = $watermark_width = $watermark_height = 0;

    if ($w_settings['type'] == 'G') {

        $watermark_image = false;
        if ($is_detailed) {
            if (!empty($w_settings['image_pair']['detailed']['absolute_path'])) {
                $watermark_image = $w_settings['image_pair']['detailed']['absolute_path'];
            }
        } elseif (!empty($w_settings['image_pair']['icon']['absolute_path'])) {
            $watermark_image = $w_settings['image_pair']['icon']['absolute_path'];
        }

        list($watermark_width, $watermark_height, $watermark_mime_type) = fn_get_image_size($watermark_image);

        if (empty($watermark_image) || !$watermark = fn_create_image_from_file($watermark_image, $watermark_mime_type)) {
            return false;
        }

    } else {
        $font_path = Registry::get('config.dir.lib') . 'other/fonts/' . $w_settings['font'] . '.ttf';

        if (!is_file($font_path) || empty($w_settings['text'])) {
            return false;
        }

        if ($is_detailed) {
            $font_size = $w_settings['font_size_detailed'];
        } else {
            $font_size = $w_settings['font_size_icon'];
        }

        if (empty($font_size)) {
            return false;
        }

        $ttfbbox = imagettfbbox($font_size, 0, $font_path, $w_settings['text']);
        $watermark_height = abs($ttfbbox[7]);
        $watermark_width = abs($ttfbbox[2]);
    }

    if (empty($watermark_width) || empty($watermark_height)) {
        return false;
    }

    // Paddings
    $delta_x = 3;
    $delta_y = 3;

    $new_wt_width = $watermark_width;
    $new_wt_height = $watermark_height;

    if ($new_wt_width + $delta_x > $original_width) {
        $new_wt_height = $new_wt_height * ($original_width - $delta_x)/ $new_wt_width;
        $new_wt_width = $original_width - $delta_x;
    }

    if ($new_wt_height > $original_height) {
        $new_wt_width = $new_wt_width * ($original_height - $delta_y)/ $new_wt_height;
        $new_wt_height = $original_height - $delta_y;
    }

    if ($w_settings['vertical_position'] == 'top') {
        $dest_y = $delta_y;
    } elseif ($w_settings['vertical_position'] == 'center') {
        $dest_y = (int) (($original_height - $new_wt_height)/ 2);
    } elseif ($w_settings['vertical_position'] == 'bottom') {
        $dest_y = $original_height - $new_wt_height - $delta_y;
    }

    if ($w_settings['horizontal_position'] == 'left') {
        $dest_x =  $delta_x;
    } elseif ($w_settings['horizontal_position'] == 'center') {
        $dest_x = (int) (($original_width - $new_wt_width)/ 2);
    } elseif ($w_settings['horizontal_position'] == 'right') {
        $dest_x = $original_width - $new_wt_width - $delta_x;
    }

    if ($dest_x < 1) {
        $dest_x = 1;
    }

    if ($dest_y < 1) {
        $dest_y = 1;
    }

    if ($w_settings['type'] == 'G') {
        imagecolortransparent($watermark, imagecolorat($watermark, 0, 0));
        if (function_exists('imageantialias')) {
            imageantialias($image, true);
        }
        $result = imagecopyresampled($image, $watermark, $dest_x, $dest_y, 0, 0, $new_wt_width, $new_wt_height, $watermark_width, $watermark_height);
        imagedestroy($watermark);
    } else {
        if ($w_settings['font_color'] == 'white') {
            $font_color = imagecolorallocate($image, 255, 255, 255);
        } elseif ($w_settings['font_color'] == 'black') {
            $font_color = imagecolorallocate($image, 0, 0, 0);
        } elseif ($w_settings['font_color'] == 'gray') {
            $font_color = imagecolorallocate($image, 120, 120, 120);
        } elseif ($w_settings['font_color'] == 'clear_gray') {
            $font_color = imagecolorallocatealpha($image, 120, 120, 120, WATERMARK_FONT_ALPHA);
        }

        $result = imagettftext($image, $font_size, 0, $dest_x, $dest_y + $font_size, $font_color, $font_path, $w_settings['text']);
    }

    if ($result === false) {
        return false;
    }

    $ext = fn_get_image_extension($original_mime_type);

    ob_start();
    if ($ext == 'gif') {
        $result = imagegif($image);
    } elseif ($ext == 'jpg') {
        $result = imagejpeg($image, null, 85);
    } elseif ($ext == 'png') {
        $result = imagepng($image, null, 8);
    }
    $content = ob_get_clean();
    imagedestroy($image);
    Storage::instance('images')->put($watermarked_image, array(
        'contents' => $content
    ));

    return $result;
}

function fn_create_image_from_file($path, $mime_type)
{
    $ext = fn_get_image_extension($mime_type);

    if ($ext == 'gif') {
        $image = imagecreatefromgif($path);
    } elseif ($ext == 'jpg') {
        $image = imagecreatefromjpeg($path);
    } elseif ($ext == 'png') {
        $image = imagecreatefrompng($path);
    } else {
        return false;
    }

    return $image;
}

function fn_watermarks_images_access_info()
{
    $is_applied = false;

    $option_types = fn_get_apply_watermark_options();
    foreach ($option_types as $options) {
        foreach ($options as $name => $option_id) {
            if (Registry::get('addons.watermarks.' . $name) == 'Y') {
                $is_applied = true;
                break;
            }
        }
    }

    if ($is_applied) {
        if (fn_allowed_for('ULTIMATE')) {
            $img_instr = "# Rewrite watermarks rules\n" .
                "<IfModule mod_rewrite.c>\n" .
                "RewriteEngine on\n" .
                "RewriteCond %{REQUEST_URI} \/images\/(product|category|detailed|thumbnails)\/*\n" .
                "RewriteCond %{REQUEST_FILENAME} -f\n" .
                "RewriteRule .(gif|jpeg|jpg|png)$ " . DIR_ROOT . "/" . fn_url('watermark.create', 'C', 'rel') . " [NC]\n" .
                "</IfModule>\n" .
                "# /Rewrite watermarks rules";
        } else {
            $img_instr = "# Rewrite watermarks rules\n" .
                "<IfModule mod_rewrite.c>\n" .
                "RewriteEngine on\n" .
                "RewriteCond %{REQUEST_URI} \/images\/(product|category|detailed|thumbnails)\/*\n" .
                "RewriteCond %{REQUEST_FILENAME} -f\n" .
                "RewriteRule (.*)$ ./watermarked/$1 [NC]\n" .
                "</IfModule>\n" .
                "# /Rewrite watermarks rules";
        }

        $img_instr = nl2br(htmlentities($img_instr));

        $wt_instr = "# Generate watermarks rules\n" .
            "<IfModule mod_rewrite.c>\n" .
            "RewriteEngine on\n" .
            "RewriteCond %{REQUEST_FILENAME} !-f\n" .
            "RewriteRule .(gif|jpeg|jpg|png)$ " . DIR_ROOT . "/" . fn_url('watermark.create', 'C', 'rel') . " [NC]\n" .
            "</IfModule>\n" .
            "# /Generate watermarks rules";
        $wt_instr = nl2br(htmlentities($wt_instr));

        $res = '<h2 class="subheader">' . __('wt_images_access_info') . '</h2>' .
            '<p>' . __('wt_images_access_description') . '</p>' .
            '<p><code>' . $img_instr . '</code></p>' .
            '<p>' . __('wt_watermarks_access_description') . '</p>' .
            '<p><code>' .  $wt_instr . '</code></p>' .
            '<p>' .__('wt_access_note') . '</p>';

        return $res;
    }

    return '';
}

function fn_settings_actions_addons_post_watermarks($status)
{
    if ($status == 'D') {
        fn_clear_watermarks();
    }
}

function fn_clear_watermarks()
{
    fn_set_notification('W', __('warning'), __('wt_access_warning'));
}
