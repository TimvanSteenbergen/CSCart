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
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//
// Get image
//
function fn_get_image($image_id, $object_type, $lang_code = CART_LANGUAGE, $get_all_alts = false)
{
    $path = $object_type;

    if (!empty($image_id) && !empty($object_type)) {
        $image_data = db_get_row("SELECT ?:images.image_id, ?:images.image_path, ?:common_descriptions.description as alt, ?:images.image_x, ?:images.image_y FROM ?:images LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:images.image_id AND ?:common_descriptions.object_holder = 'images' AND ?:common_descriptions.lang_code = ?s  WHERE ?:images.image_id = ?i", $lang_code, $image_id);
        if ($get_all_alts && count(fn_get_translation_languages()) > 1) {
            $image_data['alt'] = db_get_hash_single_array('SELECT description, lang_code FROM ?:common_descriptions WHERE object_id = ?i AND object_holder = ?s', array('lang_code', 'description'), $image_data['image_id'], 'images');
        }
    }

    fn_attach_absolute_image_paths($image_data, $object_type);

    return (!empty($image_data) ? $image_data : false);
}

//
// Attach image paths
//
function fn_attach_absolute_image_paths(&$image_data, $object_type)
{
    $image_id = !empty($image_data['images_image_id'])? $image_data['images_image_id'] : $image_data['image_id'];
    $path = $object_type . '/' . floor($image_id / MAX_FILES_IN_DIR);

    $image_name = '';
    $image_data['relative_path'] = $image_data['http_image_path'] = $image_data['absolute_path'] = '';

    if (!empty($image_data['image_path'])) {
        $image_name = $image_data['image_path'];
        $image_data['relative_path'] = $path . '/' . $image_name;
        $image_data['http_image_path'] = Storage::instance('images')->getUrl($path . '/' . $image_name, 'http');
        $image_data['absolute_path'] = Storage::instance('images')->getAbsolutePath($path . '/' . $image_name);
        $image_data['image_path'] = Storage::instance('images')->getUrl($path . '/' . $image_name);
    }

    fn_set_hook('attach_absolute_image_paths', $image_data, $object_type, $path, $image_name);

    return $image_data;
}

/**
 * Function creates or updates image
 *
 * @param mixed $image_data Array with image data
 * @param int $image_id Image ID
 * @param string $image_type Type (object) of image (may be product, category, and so on)
 * @param string $lang_code 2 letters language code
 * @return int Updated or inserted image ID. False on failure.
 */
function fn_update_image($image_data, $image_id = 0, $image_type = 'product', $lang_code = CART_LANGUAGE)
{
    $images_path = $image_type . '/';
    $_data = array();

    if (empty($image_id)) {
        $max_id = db_get_next_auto_increment_id('images');
        $img_id_subdir = floor($max_id / MAX_FILES_IN_DIR) . "/";
    } else {
        $img_id_subdir = floor($image_id / MAX_FILES_IN_DIR) . "/";
    }
    $images_path .= $img_id_subdir;

    list($_data['image_x'], $_data['image_y'], $mime_type) = fn_get_image_size($image_data['path']);

    // Get the real image type
    $ext = fn_get_image_extension($mime_type);
    if (strpos($image_data['name'], '.') === false) {
        $image_data['name'] .= '.' . $ext;
    }

    // Check if image path already set
    $image_path = db_get_field("SELECT image_path FROM ?:images WHERE image_id = ?i", $image_id);

    // Delete existing image
    if (!empty($image_path)) {
        Storage::instance('images')->delete($images_path . $image_path);

        // Clear all existing thumbnails
        fn_delete_image_thumbnails($images_path . $image_path);
    }

    fn_set_hook('update_image', $image_data, $image_id, $image_type, $images_path, $_data);

    $params = array(
        'file' => $image_data['path'],
    );

    if (!empty($image_data['params'])) {
        $params = fn_array_merge($params, $image_data['params']);
    }

    list($_data['image_size'], $_data['image_path']) = Storage::instance('images')->put($images_path . $image_data['name'], $params);

    $_data['image_path'] = fn_basename($_data['image_path']); // we need to store file name only

    if (!empty($image_id)) {
        db_query("UPDATE ?:images SET ?u WHERE image_id = ?i", $_data, $image_id);
    } else {
        $image_id = db_query("INSERT INTO ?:images ?e", $_data);
    }

    return $image_id;
}

function fn_add_image_link($pair_target_id, $pair_id)
{
    $pair_data = db_get_row("SELECT * FROM ?:images_links WHERE pair_id = ?i", $pair_id);
    unset($pair_data['pair_id']);
    $pair_data['object_id'] = $pair_target_id;

    return db_query("INSERT INTO ?:images_links ?e", $pair_data);
}

function fn_get_count_image_link($image_id)
{
    return db_get_field("SELECT COUNT(*) FROM ?:images_links WHERE image_id = ?i OR detailed_id = ?i", $image_id, $image_id);
}

//
// Delete image
//
function fn_delete_image($image_id, $pair_id, $object_type = 'product')
{
    if (AREA == 'A' && fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id') && $object_type == 'category') {
        return false;
    }

    $_image_file = db_get_field("SELECT image_path FROM ?:images WHERE image_id = ?i", $image_id);
    if (empty($_image_file)) {
        return false;
    }

    fn_set_hook('delete_image_pre', $image_id, $pair_id, $object_type);

    db_query("UPDATE ?:images_links SET " . ($object_type == 'detailed' ? 'detailed_id' : 'image_id') . " = '0' WHERE pair_id = ?i", $pair_id);
    $_ids = db_get_row("SELECT image_id, detailed_id FROM ?:images_links WHERE pair_id = ?i", $pair_id);

    if (empty($_ids['image_id']) && empty($_ids['detailed_id'])) {
        db_query("DELETE FROM ?:images_links WHERE pair_id = ?i", $pair_id);
    }

    if (fn_get_count_image_link($image_id) == 0) {

        $img_id_subdir = floor($image_id / MAX_FILES_IN_DIR);
        $_image_file = $object_type . '/' . $img_id_subdir . '/' . $_image_file;

        Storage::instance('images')->delete($_image_file);

        db_query("DELETE FROM ?:images WHERE image_id = ?i", $image_id);
        db_query("DELETE FROM ?:common_descriptions WHERE object_id = ?i AND object_holder = 'images'", $image_id);

        // Clear all existing thumbnails
        fn_delete_image_thumbnails($_image_file);
    }

    fn_set_hook('delete_image', $image_id, $pair_id, $object_type, $_image_file);

    return true;
}

/**
 * Deletes all thumbnails of specified file
 *
 * @param string $filename file name
 * @param string $prefix path prefix
 * @return boolean always true
 */
function fn_delete_image_thumbnails($filename, $prefix = '')
{
    $filename = fn_substr($filename, 0, strrpos($filename, '.'));

    if (!empty($filename)) {
        Storage::instance('images')->deleteByPattern($prefix . 'thumbnails/*/*/' . $filename . '*');
    }

    return true;
}

//
// Get image pair(s)
//
function fn_get_image_pairs($object_ids, $object_type, $pair_type, $get_icon = true, $get_detailed = true, $lang_code = CART_LANGUAGE)
{
    $icon_pairs = $detailed_pairs = $pairs_data = array();

    $cond = is_array($object_ids)? db_quote("AND ?:images_links.object_id IN (?n)", $object_ids) : db_quote("AND ?:images_links.object_id = ?i", $object_ids);

    if ($get_icon == true || $get_detailed == true) {
        if ($get_icon == true) {
            $join_cond = "?:images_links.image_id = ?:images.image_id";
            $icon_pairs = db_get_array(
                    "SELECT ?:images_links.*, ?:images.image_path, ?:common_descriptions.description AS alt, ?:images.image_x, ?:images.image_y, ?:images.image_id as images_image_id"
                    . " FROM ?:images_links"
                    . " LEFT JOIN ?:images ON $join_cond"
                    . " LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:images.image_id AND ?:common_descriptions.object_holder = 'images' AND ?:common_descriptions.lang_code = ?s"
                    . " WHERE ?:images_links.object_type = ?s AND ?:images_links.type = ?s $cond"
                    . " ORDER BY ?:images_links.position, ?:images_links.pair_id",
                    $lang_code, $object_type, $pair_type
                );
        }

        if ($get_detailed == true) {
            $join_cond = db_quote("?:images_links.detailed_id = ?:images.image_id");
            $detailed_pairs = db_get_array(
                    "SELECT ?:images_links.*, ?:images.image_path, ?:common_descriptions.description AS alt, ?:images.image_x, ?:images.image_y, ?:images.image_id as images_image_id"
                    . " FROM ?:images_links"
                    . " LEFT JOIN ?:images ON $join_cond"
                    . " LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:images.image_id AND ?:common_descriptions.object_holder = 'images' AND ?:common_descriptions.lang_code = ?s"
                    . " WHERE ?:images_links.object_type = ?s AND ?:images_links.type = ?s $cond"
                    . " ORDER BY ?:images_links.position, ?:images_links.pair_id",
                    $lang_code, $object_type, $pair_type
                );
        }

        foreach ((array) $object_ids as $object_id) {
            $pairs_data[$object_id] = array();
        }

        // Convert the received data to the standard format in order to keep the backward compatibility
        foreach ($icon_pairs as $pair) {
            $_pair = array(
                'pair_id' => $pair['pair_id'],
                'image_id' => $pair['image_id'],
                'detailed_id' => $pair['detailed_id'],
                'position' => $pair['position'],
            );

            if (!empty($pair['images_image_id'])) { //get icon data if exist
                $icon = fn_attach_absolute_image_paths($pair, $object_type);

                $_pair['icon'] = array(
                    'image_path' => $icon['image_path'],
                    'alt' => $icon['alt'],
                    'image_x' => $icon['image_x'],
                    'image_y' => $icon['image_y'],
                    'http_image_path' => $icon['http_image_path'],
                    'absolute_path' => $icon['absolute_path'],
                    'relative_path' => $icon['relative_path']
                );
            }

            $pairs_data[$pair['object_id']][$pair['pair_id']] = $_pair;
        }// -foreach icon_pairs

        foreach ($detailed_pairs as $pair) {
            $pair_id = $pair['pair_id'];
            $object_id = $pair['object_id'];

            if (!empty($pairs_data[$object_id][$pair_id]['detailed_id'])) {
                $detailed = fn_attach_absolute_image_paths($pair, 'detailed');
                $pairs_data[$object_id][$pair_id]['detailed'] = array(
                    'image_path' => $detailed['image_path'],
                    'alt' => $detailed['alt'],
                    'image_x' => $detailed['image_x'],
                    'image_y' => $detailed['image_y'],
                    'http_image_path' => $detailed['http_image_path'],
                    'absolute_path' => $detailed['absolute_path'],
                    'relative_path' => $detailed['relative_path']
                );
            } elseif (empty($pairs_data[$object_id][$pair_id]['pair_id'])) {
                $pairs_data[$object_id][$pair_id] = array(
                    'pair_id' => $pair['pair_id'],
                    'image_id' => $pair['image_id'],
                    'detailed_id' => $pair['detailed_id'],
                    'position' => $pair['position'],
                );

                if (!empty($pair['images_image_id'])) { //get detailed data if exist
                    $detailed = fn_attach_absolute_image_paths($pair, 'detailed');
                    $pairs_data[$object_id][$pair_id]['detailed'] = array(
                        'image_path' => $detailed['image_path'],
                        'alt' => $detailed['alt'],
                        'image_x' => $detailed['image_x'],
                        'image_y' => $detailed['image_y'],
                        'http_image_path' => $detailed['http_image_path'],
                        'absolute_path' => $detailed['absolute_path'],
                        'relative_path' => $detailed['relative_path']
                    );
                }
            }
        }// -foreach detailed_pairs

    } else {
        $pairs_data = db_get_hash_multi_array("SELECT pair_id, image_id, detailed_id, object_id FROM ?:images_links WHERE object_type = ?s AND type = ?s $cond", array('object_id', 'pair_id'), $object_type, $pair_type);
    }

    if (is_array($object_ids)) {
        return $pairs_data;
    } else {
        if ($pair_type == 'A') {
            return $pairs_data[$object_ids];
        } else {
            return !empty($pairs_data[$object_ids])? reset($pairs_data[$object_ids]) : array();
        }
    }
}

//
// Create/Update image pairs (icon -> detailed image)
//
function fn_update_image_pairs($icons, $detailed, $pairs_data, $object_id = 0, $object_type = 'product_lists', $object_ids = array (), $update_alt_desc = true, $lang_code = CART_LANGUAGE)
{
    $pair_ids = array();

    if (!empty($pairs_data)) {
        foreach ($pairs_data as $k => $p_data) {
            $data = array();
            $pair_id = !empty($p_data['pair_id']) ? $p_data['pair_id'] : 0;
            $o_id = !empty($object_id) ? $object_id : ((!empty($p_data['object_id'])) ? $p_data['object_id'] : 0);

            if ($o_id == 0 && !empty($object_ids[$k])) {
                $o_id = $object_ids[$k];
            } elseif (!empty($object_ids) && empty($object_ids[$k])) {
                continue;
            }

            // Check if main pair is exists
            if (empty($pair_id) && !empty($p_data['type']) && $p_data['type'] == 'M') {
                $pair_data = db_get_row("SELECT pair_id, image_id, detailed_id FROM ?:images_links WHERE object_id = ?i AND object_type = ?s AND type = ?s", $o_id, $object_type, $p_data['type']);
                $pair_id = !empty($pair_data['pair_id']) ? $pair_data['pair_id'] : 0;
            } else {
                $pair_data = db_get_row("SELECT image_id, detailed_id FROM ?:images_links WHERE pair_id = ?i", $pair_id);
                if (empty($pair_data)) {
                    $pair_id = 0;
                }
            }

            // Update detailed image
            if (!empty($detailed[$k]) && !empty($detailed[$k]['size'])) {
                if (fn_get_image_size($detailed[$k]['path'])) {
                    $data['detailed_id'] = fn_update_image($detailed[$k], !empty($pair_data['detailed_id']) ? $pair_data['detailed_id'] : 0, 'detailed');
                }
            }

            // Update icon
            if (!empty($icons[$k]) && !empty($icons[$k]['size'])) {
                if (fn_get_image_size($icons[$k]['path'])) {
                    $data['image_id'] = fn_update_image($icons[$k], !empty($pair_data['image_id']) ? $pair_data['image_id'] : 0, $object_type);
                }
            }

            // Update alt descriptions
            if (((empty($data) && !empty($pair_id)) || !empty($data)) && $update_alt_desc == true) {
                $image_ids = array();
                if (!empty($pair_id)) {
                    $image_ids = db_get_row("SELECT image_id, detailed_id FROM ?:images_links WHERE pair_id = ?i", $pair_id);
                }

                $image_ids = fn_array_merge($image_ids, $data);

                $fields = array('detailed', 'image');
                foreach ($fields as $field) {
                    if (!empty($image_ids[$field . '_id']) && isset($p_data[$field . '_alt'])) {
                        if (!is_array($p_data[$field . '_alt'])) {
                            $_data = array (
                                'description' => empty($p_data[$field . '_alt']) ? '' : trim($p_data[$field . '_alt']),
                                'object_holder' => 'images'
                            );

                            // check, if this is new record, create new descriptions for all languages
                            $is_exists = db_get_field('SELECT object_id FROM ?:common_descriptions WHERE object_id = ?i AND lang_code = ?s AND object_holder = ?s', $image_ids[$field . '_id'], $lang_code, 'images');
                            if (!$is_exists) {
                                fn_create_description('common_descriptions', 'object_id', $image_ids[$field . '_id'], $_data);
                            } else {
                                db_query('UPDATE ?:common_descriptions SET ?u WHERE object_id = ?i AND lang_code = ?s AND object_holder = ?s', $_data, $image_ids[$field . '_id'], $lang_code, 'images');
                            }
                        } else {
                            foreach ($p_data[$field . '_alt'] as $lc => $_v) {
                                $_data = array (
                                    'object_id' => $image_ids[$field . '_id'],
                                    'description' => empty($_v) ? '' : trim($_v),
                                    'lang_code' => $lc,
                                    'object_holder' => 'images'
                                );
                                db_query("REPLACE INTO ?:common_descriptions ?e", $_data);
                            }
                        }
                    }
                }
            }

            if (empty($data)) {
                continue;
            }

            // Pair is exists
            $data['position'] = !empty($p_data['position']) ? $p_data['position'] : 0; // set data position

            if (!empty($pair_id)) {
                db_query("UPDATE ?:images_links SET ?u WHERE pair_id = ?i", $data, $pair_id);
            } else {
                $data['type'] = $p_data['type']; // set link type
                $data['object_id'] = $o_id; // assign pair to object
                $data['object_type'] = $object_type;
                $pair_id = db_query("INSERT INTO ?:images_links ?e", $data);
            }

            $pairs_data[$k]['pair_id'] = $pair_id;

            $pair_ids[] = $pair_id;
        }
    }

    fn_set_hook('update_image_pairs', $pair_ids, $icons, $detailed, $pairs_data, $object_id, $object_type, $object_ids, $update_alt_desc, $lang_code);

    return $pair_ids;
}

function fn_delete_image_pairs($object_id, $object_type, $pair_type = '')
{
    $cond = '';

    if ($pair_type  === 'A') {
        $cond .= db_quote("AND type = 'A'");
    } elseif ($pair_type === 'M') {
        $cond .= db_quote("AND type = 'M'");
    }

    $pair_ids = db_get_fields("SELECT pair_id FROM ?:images_links WHERE object_id = ?i AND object_type = ?s ?p", $object_id, $object_type, $cond);

    foreach ($pair_ids as $pair_id) {
        fn_delete_image_pair($pair_id, $object_type);
    }

    return true;
}

//
// Delete image pair
//
function fn_delete_image_pair($pair_id, $object_type = 'product')
{
    if (!empty($pair_id)) {
        $images = db_get_row("SELECT image_id, detailed_id FROM ?:images_links WHERE pair_id = ?i", $pair_id);
        if (!empty($images)) {
            fn_delete_image($images['image_id'], $pair_id, $object_type);
            fn_delete_image($images['detailed_id'], $pair_id, 'detailed');
        }

        fn_set_hook('delete_image_pair', $pair_id, $object_type);

        return true;
    }

    return false;
}

/**
 * Delete all images pairs for object
 */
function fn_clean_image_pairs($object_id, $object_type)
{
    $pair_data = db_get_hash_array("SELECT pair_id, image_id, detailed_id, type FROM ?:images_links WHERE object_id = ?i AND object_type = ?s", 'pair_id', $object_id, $object_type);

    foreach ($pair_data as $pair_id => $p_data) {
        fn_delete_image_pair($pair_id, $object_type);
    }
}

//
// Clone image pairs
//
function fn_clone_image_pairs($target_object_id, $object_id, $object_type, $lang_code = CART_LANGUAGE)
{
    // Get all pairs
    $pair_data = db_get_hash_array("SELECT pair_id, image_id, detailed_id, type FROM ?:images_links WHERE object_id = ?i AND object_type = ?s", 'pair_id', $object_id, $object_type);

    if (empty($pair_data)) {
        return false;
    }

    $icons = $detailed = $pairs_data = array();

    foreach ($pair_data as $pair_id => $p_data) {
        if (!empty($p_data['image_id'])) {
            $icons[$pair_id] = fn_get_image($p_data['image_id'], $object_type, $lang_code, true);

            if (!empty($icons[$pair_id])) {
                $p_data['image_alt'] = empty($icons[$pair_id]['alt']) ? '' : $icons[$pair_id]['alt'];

                $tmp_name = fn_create_temp_file();
                Storage::instance('images')->export($icons[$pair_id]['relative_path'], $tmp_name);
                $name = fn_basename($icons[$pair_id]['image_path']);

                $icons[$pair_id] = array(
                    'path' => $tmp_name,
                    'size' => filesize($tmp_name),
                    'error' => 0,
                    'name' => $name,
                );
            }
        }
        if (!empty($p_data['detailed_id'])) {
            $detailed[$pair_id] = fn_get_image($p_data['detailed_id'], 'detailed', $lang_code, true);
            if (!empty($detailed[$pair_id])) {
                $p_data['detailed_alt'] = empty($detailed[$pair_id]['alt']) ? '' : $detailed[$pair_id]['alt'];

                $tmp_name = fn_create_temp_file();

                Storage::instance('images')->export($detailed[$pair_id]['relative_path'], $tmp_name);

                $name = fn_basename($detailed[$pair_id]['image_path']);

                $detailed[$pair_id] = array(
                    'path' => $tmp_name,
                    'size' => filesize($tmp_name),
                    'error' => 0,
                    'name' => $name,
                );
            }
        }

        $pairs_data = array(
            $pair_id => array(
                'type' => $p_data['type'],
                'image_alt' => (!empty($p_data['image_alt'])) ? $p_data['image_alt'] : '',
                'detailed_alt' => (!empty($p_data['detailed_alt'])) ? $p_data['detailed_alt'] : '',
            )
        );

        fn_update_image_pairs($icons, $detailed, $pairs_data, $target_object_id, $object_type, array(), true, $lang_code);
    }
}

// ----------- Utility functions -----------------

/**
 * Resizes image
 * @param string $src source image path
 * @param integer $new_width new image width
 * @param integer $new_height new image height
 * @param string $bg_color new image background color
 * @param array $custom_settings custom convertion settings
 * @return array - new image contents and format
 */
function fn_resize_image($src, $new_width = 0, $new_height = 0, $bg_color = '#ffffff', $custom_settings = array())
{
    static $notification_set = false;
    static $gd_settings = array();
    if (empty($gd_settings)) {
        $gd_settings = Settings::instance()->getValues('Thumbnails');
    }

    $settings = !empty($custom_settings) ? $custom_settings : $gd_settings;

    if (file_exists($src) && (!empty($new_width) || !empty($new_height)) && extension_loaded('gd')) {
        $img_functions = array(
            'png' => function_exists('imagepng'),
            'jpg' => function_exists('imagejpeg'),
            'gif' => function_exists('imagegif'),
        );

        list($width, $height, $mime_type) = fn_get_image_size($src);
        if (empty($width) || empty($height)) {
            return false;
        }

        $ext = fn_get_image_extension($mime_type);
        if (empty($img_functions[$ext])) {
            if ($notification_set == false) {
                fn_set_notification('E', __('error'), __('error_image_format_not_supported', array(
                    '[format]' => $ext
                )));
                $notification_set = true;
            }

            return false;
        }

        if (empty($new_width) || empty($new_height)) {
            if ($width < $new_width) {
                $new_width = $width;
            }
            if ($height < $new_height) {
                $new_height = $height;
            }
        }

        $dst_width = $new_width;
        $dst_height = $new_height;

        if (empty($new_height)) { // if we passed width only, calculate height
            $dst_height = $new_height = ($height / $width) * $new_width;

        } elseif (empty($new_width)) { // if we passed height only, calculate width
            $dst_width = $new_width = ($width / $height) * $new_height;

        } else { // we passed width and height, we need to fit image in this sizes
            if ($new_width * $height / $width > $dst_height) {
                $new_width = $width * $dst_height / $height;
            }
            $new_height = ($height / $width) * $new_width;
            if ($new_height * $width / $height > $dst_width) {
                $new_height = $height * $dst_width / $width;
            }
            $new_width = ($width / $height) * $new_height;

            $make_box = true;
        }

        $new_width = intval($new_width);
        $new_height = intval($new_height);

        $dst = imagecreatetruecolor($dst_width, $dst_height);

        if (function_exists('imageantialias')) {
            imageantialias($dst, true);
        }

        if ($ext == 'gif') {
            $new = imagecreatefromgif($src);
        } elseif ($ext == 'jpg') {
            $new = imagecreatefromjpeg($src);
        } elseif ($ext == 'png') {
            $new = imagecreatefrompng($src);
        }

        list($r, $g, $b) = (empty($bg_color)) ? fn_parse_rgb('#ffffff') : fn_parse_rgb($bg_color);
        $c = imagecolorallocate($dst, $r, $g, $b);

        if (empty($bg_color) && ($ext == 'png' || $ext == 'gif')) {
            if (function_exists('imagecolorallocatealpha') && function_exists('imagecolortransparent') && function_exists('imagesavealpha') && function_exists('imagealphablending')) {
                $c = imagecolorallocatealpha($dst, 255, 255, 255, 127);
                imagecolortransparent($dst, $c);
                imagesavealpha($dst, true);
                imagealphablending($dst, false);
            }
        }

        imagefilledrectangle($dst, 0, 0, $dst_width, $dst_height, $c);

        if (!empty($make_box)) {
            $x = intval(($dst_width - $new_width) / 2);
            $y = intval(($dst_height - $new_height) / 2);
        } else {
            $x = 0;
            $y = 0;
        }

        imagecopyresampled($dst, $new, $x, $y, 0, 0, $new_width, $new_height, $width, $height);

        // Free memory from image
        imagedestroy($new);

        if ($settings['convert_to'] == 'original') {
            $convert_to = $ext;
        } elseif (!empty($img_functions[$settings['convert_to']])) {
            $convert_to = $settings['convert_to'];
        } else {
            $convert_to = key($img_functions);
        }

        ob_start();
        if ($convert_to == 'gif') {
            imagegif($dst);
        } elseif ($convert_to == 'jpg') {
            imagejpeg($dst, null, $settings['jpeg_quality']);
        } elseif ($convert_to == 'png') {
            imagepng($dst);
        }
        $content = ob_get_clean();
        imagedestroy($dst);

        return array($content, $convert_to);
    }

    return false;
}

//
// Check supported GDlib formats
//
function fn_check_gd_formats()
{
    $avail_formats = array(
        'original' => __('same_as_source'),
    );

    if (function_exists('imagegif')) {
        $avail_formats['gif'] = 'GIF';
    }
    if (function_exists('imagejpeg')) {
        $avail_formats['jpg'] = 'JPEG';
    }
    if (function_exists('imagepng')) {
        $avail_formats['png'] = 'PNG';
    }

    return $avail_formats;
}

//
// Get image extension by MIME type
//
function fn_get_image_extension($image_type)
{
    static $image_types = array (
        'image/gif' => 'gif',
        'image/pjpeg' => 'jpg',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'application/x-shockwave-flash' => 'swf',
        'image/psd' => 'psd',
        'image/bmp' => 'bmp',
        'image/x-icon' => 'ico'
    );

    return isset($image_types[$image_type]) ? $image_types[$image_type] : false;
}

/**
 * Returns image width, height, mime type and local path to image
 *
 * @param string $file path to image
 * @return array array with width, height, mime type and path
 */
function fn_get_image_size($file)
{
    // File is url, get it and store in temporary directory
    if (strpos($file, '://') !== false) {
        $tmp = fn_create_temp_file();

        if (fn_put_contents($tmp, fn_get_contents($file)) == 0) {
            return false;
        }

        $file = $tmp;
    }

    list($w, $h, $t, $a) = @getimagesize($file);

    if (empty($w)) {
        return false;
    }

    $t = image_type_to_mime_type($t);

    return array($w, $h, $t, $file);
}

function fn_attach_image_pairs($name, $object_type, $object_id = 0, $lang_code = CART_LANGUAGE, $object_ids = array ())
{
    $icons = fn_filter_uploaded_data($name . '_image_icon', array('png', 'gif', 'jpg', 'jpeg', 'ico'));
    $detailed = fn_filter_uploaded_data($name . '_image_detailed', array('png', 'gif', 'jpg', 'jpeg', 'ico'));
    $pairs_data = !empty($_REQUEST[$name . '_image_data']) ? $_REQUEST[$name . '_image_data'] : array();

    return fn_update_image_pairs($icons, $detailed, $pairs_data, $object_id, $object_type, $object_ids, true, $lang_code);
}

/**
 * Generate thumbnail with given size from image
 *
 * @param string $image_path Path to image
 * @param int $width Thumbnail width
 * @param int $height Thumbnail height
 * @param bool $lazy lazy generation - returns script URL that generates thumbnail
 * @return array array with width, height, mime type and path
 */
function fn_generate_thumbnail($image_path, $width, $height = 0, $lazy = false)
{
    /**
     * Actions before thumbnail generate
     *
     * @param string $image_path Path to image
     * @param int    $width      Width of thumbnail
     * @param int    $height     Height of thumbnail
     * @param bool   $make_box   If true create rectangle border
     */
    fn_set_hook('generate_thumbnail_pre', $image_path, $width, $height, $make_box);

    if (empty($image_path)) {
        return '';
    }

    $filename = 'thumbnails/' . $width . (empty($height) ? '' : '/' . $height) . '/' . $image_path;
    if (Registry::get('settings.Thumbnails.convert_to') != 'original') {
        $filename = preg_replace("/\.[^.]*?$/", "." . Registry::get('settings.Thumbnails.convert_to'), $filename);
    }

    $th_filename = '';

    if ($lazy || Storage::instance('images')->isExist($filename)) {
        $th_filename = $filename;
    } else {

        // for lazy thumbnails: find original filename
        if (Registry::get('config.tweaks.lazy_thumbnails') && Registry::get('settings.Thumbnails.convert_to') != 'original' && !Storage::instance('images')->isExist($image_path)) {
            foreach (array('png', 'jpg', 'jpeg') as $ext) {
                $image_path = preg_replace("/\.[^.]*?$/", "." . $ext, $image_path);
                if (Storage::instance('images')->isExist($image_path)) {
                    break;
                }
            }
        }

        /**
         * Actions before thumbnail generate, if thumbnail is not exists, after validations
         *
         * @param string $real_path Real path to image
         * @param string $lazy lazy generation - returns script URL that generates thumbnail
         */
        fn_set_hook('generate_thumbnail_file_pre', $image_path, $lazy, $filename, $width, $height);

        list(, , ,$tmp_path) = fn_get_image_size(Storage::instance('images')->getAbsolutePath($image_path));

        if (!empty($tmp_path)) {
            list($cont, $format) = fn_resize_image($tmp_path, $width, $height, Registry::get('settings.Thumbnails.thumbnail_background_color'));

            if (!empty($cont)) {
                list(, $th_filename) = Storage::instance('images')->put($filename, array(
                    'contents' => $cont,
                    'caching' => true
                ));
            }
        }
    }

    /**
     * Actions after thumbnail generate
     *
     * @param string $th_filename Thumbnail path
     * @param string $lazy        lazy generation - returns script URL that generates thumbnail
     */
    fn_set_hook('generate_thumbnail_post', $th_filename, $lazy);

    return !empty($th_filename) ? Storage::instance('images')->getUrl($th_filename) : '';
}

function fn_parse_rgb($color)
{
    $r = hexdec(substr($color, 1, 2));
    $g = hexdec(substr($color, 3, 2));
    $b = hexdec(substr($color, 5, 2));

    return array($r, $g, $b);
}

function fn_image_to_display($images, $image_width = 0, $image_height = 0)
{
    if (empty($images)) {
        return array();
    }

    $image_data = array();

    // image pair passed
    if (!empty($images['icon']) || !empty($images['detailed'])) {
        if (!empty($images['icon'])) {
            $original_width = $images['icon']['image_x'];
            $original_height = $images['icon']['image_y'];
            $image_path = $images['icon']['image_path'];
            $absolute_path = $images['icon']['absolute_path'];
            $relative_path = $images['icon']['relative_path'];
        } else {
            $original_width = $images['detailed']['image_x'];
            $original_height = $images['detailed']['image_y'];
            $image_path = $images['detailed']['image_path'];
            $absolute_path = $images['detailed']['absolute_path'];
            $relative_path = $images['detailed']['relative_path'];
        }

        $detailed_image_path = !empty($images['detailed']['image_path']) ? $images['detailed']['image_path'] : '';
        $alt = !empty($images['icon']['alt']) ? $images['icon']['alt'] : $images['detailed']['alt'];

    // single image passed only
    } else {
        $original_width = $images['image_x'];
        $original_height = $images['image_y'];
        $image_path = $images['image_path'];
        $alt = $images['alt'];
        $detailed_image_path = '';
        $absolute_path = $images['absolute_path'];
        $relative_path = $images['relative_path'];
    }

    if (!empty($image_height) && empty($image_width) && !empty($original_height)) {
        $image_width = intval($image_height * $original_width / $original_height);
    }

    if (!empty($image_width) && empty($image_height) && !empty($original_width)) {
        $image_height = intval($image_width * $original_height / $original_width);
    }

    if (!empty($image_width) && !empty($relative_path) && !empty($absolute_path)) {
        $image_path = fn_generate_thumbnail($relative_path, $image_width, $image_height, Registry::get('config.tweaks.lazy_thumbnails'));
    } else {
        $image_width = $original_width;
        $image_height = $original_height;
    }

    if (!empty($image_path)) {
        $image_data = array(
            'image_path' => $image_path,
            'detailed_image_path' => $detailed_image_path,
            'alt' => $alt,
            'width' => $image_width,
            'height' => $image_height,
            'absolute_path' => $absolute_path,
            'generate_image' => strpos($image_path, '&image_path=') !== false // FIXME: dirty checking
        );
    }

    /**
     * Additionally processes image data
     *
     * @param array $image_data Image data
     * @param array $images     Array with initial images
     * @param $image_width Result image width
     * @param $image_height Result image height
     */
    fn_set_hook('image_to_display_post', $image_data, $images, $image_width, $image_height);

    return $image_data;
}
