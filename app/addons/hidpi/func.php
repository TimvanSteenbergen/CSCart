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

use Tygh\Storage;
use Tygh\Registry;

/**
 * Hook: generates low-resolution image from HiDPI one
 * @param array &$image_data
 * @param int &$image_id
 * @param string &$image_type
 * @param string &$images_path
 * @param array &$_data
 */
function fn_hidpi_update_image(&$image_data, &$image_id, &$image_type, &$images_path, &$_data)
{
    // Save original image
    $filename = fn_hdpi_form_name($image_data['name']);
    Storage::instance('images')->put($images_path . $filename, array(
        'file' => $image_data['path'],
        'keep_origins' => true
    ));
    
    // Resize original image to non-hidpi resolution
    $_data['image_x'] = intval($_data['image_x'] / 2);
    $_data['image_y'] = intval($_data['image_y'] / 2);

    fn_put_contents($image_data['path'], fn_resize_image($image_data['path'], $_data['image_x'], $_data['image_y']));
}

/**
 * Hook: generates HiDPI image during thumbnail generation
 * @param string $image_path
 * @param boolean $lazy
 * @param string $filename
 * @param int $width
 * @param int $height
 */
function fn_hidpi_generate_thumbnail_file_pre($image_path, $lazy, $filename, $width, $height)
{
    if ($lazy == false) {

        list(, , ,$tmp_path) = fn_get_image_size(fn_hdpi_form_name(Storage::instance('images')->getAbsolutePath($image_path)));

        if (!empty($tmp_path)) {
            list($cont, $format) = fn_resize_image($tmp_path, $width * 2, $height * 2, Registry::get('settings.Thumbnails.thumbnail_background_color'));

            if (!empty($cont)) {
                $_filename = fn_hdpi_form_name($filename, $format);
                Storage::instance('images')->put($_filename, array(
                    'contents' => $cont,
                    'caching' => true,
                    'keep_origins' => true
                ));
            }
        }
    }
}

/**
 * Hook: deletes HiDPI image
 * @param int $image_id
 * @param int $pair_id
 * @param string $object_type
 * @param string $_image_file
 */
function fn_hidpi_delete_image($image_id, $pair_id, $object_type, $_image_file)
{
    Storage::instance('images')->delete(fn_hdpi_form_name($_image_file));
}

/**
 * Generates name for HiDPI image
 * @param string $filename original file name
 * @param string $extension target image extension, if empty - original extension used
 * @return string generated name
 */
function fn_hdpi_form_name($filename, $extension = '')
{
    if (!empty($extension)) {
        $filename = substr_replace($filename, '.' . $extension, strrpos($filename, '.'));
    }

    return  substr_replace($filename, '@2x.', strrpos($filename, '.'), 1);
}

/**
 * Show message on install addon
 */
function fn_hidpi_install()
{
    fn_set_notification('W',__('warning'), __('text_hidpi_install'));
}

/**
 * Show message on uninstall addon
 */
function fn_hidpi_uninstall()
{
    fn_set_notification('W',__('warning'), __('text_hidpi_uninstall'));
}
