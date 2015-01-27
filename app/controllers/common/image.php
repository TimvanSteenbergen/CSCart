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
use Tygh\BlockManager\RenderManager;
use Tygh\Settings;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

//
// Delete image
//
if ($mode == 'delete_image') {
    if (AREA == 'A' && !empty($auth['user_id'])) {
        fn_delete_image($_REQUEST['image_id'], $_REQUEST['pair_id'], $_REQUEST['object_type']);
        if (defined('AJAX_REQUEST')) {
            Registry::get('ajax')->assign('deleted', true);
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_SERVER['HTTP_REFERER']);
        }
    }
    exit;

//
// Delete image pair
//
} elseif ($mode == 'delete_image_pair') {
    if (AREA == 'A' && !empty($auth['user_id'])) {
        fn_delete_image_pair($_REQUEST['pair_id'], $_REQUEST['object_type']);
        if (defined('AJAX_REQUEST')) {
            Registry::get('ajax')->assign('deleted', true);
        }
    }
    exit;

} elseif ($mode == 'captcha') {

    $verification_id = $_REQUEST['verification_id'];
    if (empty($verification_id)) {
        $verification_id = 'common';
    }

    $verification_settings = Settings::instance()->getValues('Image_verification');
    $fonts = array(Registry::get('config.dir.lib') . 'other/captcha/verdana.ttf');

    $c = new PhpCaptcha($verification_id, $fonts, $verification_settings['width'], $verification_settings['height']);

    // Set string length
    $c->SetNumChars($verification_settings['string_length']);

    // Set number of distortion lines
    $c->SetNumLines($verification_settings['lines_number']);

    // Set minimal font size
    $c->SetMinFontSize($verification_settings['min_font_size']);

    // Set maximal font size
    $c->SetMaxFontSize($verification_settings['max_font_size']);

    $c->SetGridColour($verification_settings['grid_color']);

    if ($verification_settings['char_shadow'] == 'Y') {
        $c->DisplayShadow(true);
    }

    if ($verification_settings['colour'] == 'Y') {
        $c->UseColour(true);
    }

    if ($verification_settings['string_type'] == 'digits') {
        $c->SetCharSet(array(2,3,4,5,6,8,9));
    } elseif ($verification_settings['string_type'] == 'letters') {
        $c->SetCharSet(range('A','F'));
    } else {
        $c->SetCharSet(fn_array_merge(range('A','F'), array(2,3,4,5,6,8,9), false));
    }

    if (!empty($verification_settings['background_image'])) {
        $c->SetBackgroundImages(Registry::get('config.dir.root') . '/' . $verification_settings['background_image']);
    }

    $c->Create();
    exit;
} elseif ($mode == 'custom_image') {
    if (empty($_REQUEST['image'])) {
        exit();
    }

    $type = empty($_REQUEST['type']) ? 'T' : $_REQUEST['type'];

    $image_path = 'sess_data/' . fn_basename($_REQUEST['image']);

    if (Storage::instance('custom_files')->isExist($image_path)) {
        $real_path = Storage::instance('custom_files')->getAbsolutePath($image_path);
        list(, , $image_type, $tmp_path) = fn_get_image_size($real_path);

        if ($type == 'T') {
            $thumb_path = $image_path . '_thumb';

            if (!Storage::instance('custom_files')->isExist($thumb_path)) {
                // Output a thumbnail image
                list($cont, $format) = fn_resize_image($tmp_path, Registry::get('settings.Thumbnails.product_lists_thumbnail_width'), Registry::get('settings.Thumbnails.product_lists_thumbnail_height'), Registry::get('settings.Thumbnails.thumbnail_background_color'));

                if (!empty($cont)) {
                    Storage::instance('custom_files')->put($thumb_path, array(
                        'contents' => $cont
                    ));
                }
            }

            $real_path = Storage::instance('custom_files')->getAbsolutePath($thumb_path);
        }

        header('Content-type: ' . $image_type);
        fn_echo(fn_get_contents($real_path));

        exit();
    }

    // Not image file. Display spacer instead.
    header('Content-type: image/gif');
    readfile(fn_get_theme_path('[themes]/[theme]') . '/media/images/spacer.gif');

    exit();

} elseif ($mode == 'thumbnail') {

    $img = fn_generate_thumbnail($_REQUEST['image_path'], $_REQUEST['w'], $_REQUEST['h']);

    if (!empty($img)) {
        header('Content-type: ' . fn_get_file_type($img));
        fn_echo(fn_get_contents($img));
    }
    exit;
}
