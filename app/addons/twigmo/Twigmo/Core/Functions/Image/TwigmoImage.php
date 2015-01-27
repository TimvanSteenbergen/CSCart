<?php

namespace Twigmo\Core\Functions\Image;

use Tygh\Registry;
use Twigmo\Core\TwigmoConnector;

class TwigmoImage
{
    /*
     * Update additional images
     */
    final public static function updateImagesByApiData(
        $images,
        $object_id = 0,
        $object_type = 'product',
        $lang_code = CART_LANGUAGE
    ) {
        $icons = array();
        $detailed = array();
        $pair_data = array();

        foreach ($images as $image) {
            $p_data = array (
                'pair_id' => 0,
                'type' => 'A',
                'image_alt' => '',
                'detailed_alt' => !empty($image['alt']) ? $image['alt'] : '',
            );

            if (!empty($image['image_id'])) {
                $image_info = db_get_row(
                    "SELECT type, pair_id
                     FROM ?:images_links
                     WHERE object_id = ?i
                     AND object_type=?s
                     AND detailed_id = ?i",
                    $object_id,
                    $object_type,
                    $image['image_id']
                );

                if (empty($image_info) || $image_info['type'] == 'M') {
                    // ignore errors in image_id
                    // deny update/delete main detailed image
                    continue;
                }

                if (!empty($image['deleted']) && $image['deleted'] == 'Y') {
                    fn_delete_image($image['image_id'], $image_info['pair_id'], 'detailed');
                    continue;
                }

                $p_data['pair_id'] = $image_info['pair_id'];
                $p_data['image_alt'] = db_get_field(
                    "SELECT a.description
                     FROM ?:common_descriptions as a, ?:images_links as b
                     WHERE a.object_holder = ?s
                     AND a.lang_code = ?s
                     AND a.object_id = b.image_id
                     AND b.pair_id = ?i",
                    'images',
                    $lang_code,
                    $image_info['pair_id']
                );
            }

            $detailed_image = self::fn_twg_get_image_by_api_data($image);
            if (empty($image['image_id']) && empty($detailed_image)) {
                continue;
            }
            $detailed[] = $detailed_image;
            $pair_data[] = $p_data;
        }

        return fn_update_image_pairs(
            $icons,
            $detailed,
            $pair_data,
            $object_id,
            $object_type,
            array(),
            '',
            0,
            true,
            $lang_code
        );
    }

    final public static function updateIconsByApiData(
        $image,
        $object_id = 0,
        $object_type = 'product',
        $lang_code = CART_LANGUAGE
    ) {
        if (!empty($image['deleted']) && $image['deleted'] == 'Y') {
            // delete image
            $image_info = db_get_row(
                "SELECT image_id, pair_id
                 FROM ?:images_links
                 WHERE object_id = ?i
                 AND object_type=?s AND type = 'M'",
                $object_id,
                $object_type
            );

            if (!empty($image_info)) {
                fn_delete_image($image_info['image_id'], $image_info['pair_id'], $object_type);
            }

            return true;
        }

        $icon_list = array();

        if ($icon = self::fn_twg_get_image_by_api_data($image)) {
            $icon_list[] = $icon;
        }

        $detailed_alt = db_get_field(
            "SELECT a.description
             FROM ?:common_descriptions as a, ?:images_links as b
             WHERE a.object_holder = ?s
             AND a.lang_code = ?s
             AND a.object_id = b.detailed_id
             AND b.object_id = ?i
             AND b.object_type = ?s
             AND b.type = ?s",
            'images',
            $lang_code,
            $object_id,
            $object_type,
            'M'
        );

        $icon_data = array (
            'type' => 'M',
            'image_alt' => !empty($image['alt']) ? $image['alt'] : '',
            'detailed_alt' => $detailed_alt
        );

        return fn_update_image_pairs(
            $icon_list,
            array(),
            array($icon_data),
            $object_id,
            $object_type,
            array(),
            '',
            0,
            true,
            $lang_code
        );
    }

    final public static function getApiImageData(
        $image_pair,
        $type       = 'product',
        $image_type = 'icon',
        $params     = array()
    )
    {
        if (empty($image_pair)) {
            return false;
        }
        if (
            $image_type == 'detailed'
            and !empty($image_pair['detailed_id'])
            or empty($image_pair['image_id'])
            or !empty($image_pair['image_id'])
            and empty($image_pair['icon'])
        ) {
            $icon = isset($image_pair['detailed']) ? $image_pair['detailed'] : array();
        } elseif (!empty($image_pair['image_id'])) {
            $icon = $image_pair['icon'];
        }
        $icon['pair_id'] = $image_pair['pair_id'];

        if (isset($params['width']) && isset($params['height'])
            && !empty($icon['image_x']) && !empty($icon['image_y'])
            && $icon['image_x'] > 0 && $icon['image_y'] > 0
            && ($icon['image_x'] > $params['width'] || $icon['image_y'] > $params['width'])

        ) {
            $path = version_compare(PRODUCT_VERSION, '4.0.2', '<')? $icon['image_path']: $icon['relative_path'];
            $width = $params['width'];
            $height = $params['height'];
            // To avoid making box
            if (isset($params['keep_proportions'])) {
                if ($icon['image_x'] > $icon['image_y']) {
                    $height = 0;
                } else {
                    $width = 0;
                }
            }
            $icon['url'] = fn_generate_thumbnail($path, $width, $height);
            $real_path = $icon['absolute_path'];

            $size = fn_get_image_size($real_path);
            $icon['image_y'] = $size ? $size[0] : $params['height'];
            $icon['image_x'] = $size ? $size[1] : $params['width'];
        } else {
            $icon['url'] = $icon['image_path'];
        }


        // Delete unnecessary fields
        if (isset($icon['absolute_path'])) {
            unset($icon['absolute_path']);
        }


        return $icon;
    }

    /**
     * Get customer images path
     */
    final public static function getImagesPath()
    {
        $theme_path = fn_get_theme_path(
                '[relative]/[theme]',
                'C',
                fn_twg_get_current_company_id()
        );

        $path =
            $theme_path
            . '/media/images/addons/twigmo/images/';

        return $path;
    }

    /**
     * Get default logo's url for twigmo
     */
    final public static function getDefaultLogoUrl($company_id = null)
    {
        $company_id = !empty($company_id) ? $company_id : fn_twg_get_current_company_id();
        $logos = fn_get_logos($company_id, fn_twg_get_default_layout_id());
        return !empty($logos['theme']['image']['image_path']) ? $logos['theme']['image']['image_path'] : '';
    }

    /**
     * Get favicon's url for twigmo
     */
    final public static function getFaviconUrl()
    {
        $local_jsurl = Registry::get('config.twg.jsurl');
        $urls = TwigmoConnector::getMobileScriptsUrls($local_jsurl);

        return $urls['favicon'];
    }

    /**
     *
     * @param Array $params
     * @return Integer
     */
    public static function getImageId($params)
    {
        if (!empty($params['pair_id']) && !empty($params['object_type'])) {
            return db_get_field(
                'SELECT image_id FROM ?:images_links WHERE pair_id = ?i AND object_type = ?s LIMIT 1',
                $params['pair_id'],
                $params['object_type']
            );
        }
        return 0;
    }

    /*
    * Extract image from api data
    */
    private static function fn_twg_get_image_by_api_data($api_image)
    {
        if (empty($api_image['data']) || (empty($api_image['file_name']) && empty($api_image['type']))) {
            return false;
        }

        if (empty($api_image['file_name'])) {
            $api_image['file_name'] = 'image_' . strtolower(fn_generate_code('', 4)) . '.' . $api_image['type'];
        }

        $_data = base64_decode($api_image['data']);

        $image = array (
            'name' => $api_image['file_name'],
            'path' => fn_create_temp_file(),
            'size' => strlen($_data)
        );

        $file_descriptor = fopen($image['path'], 'wb');

        if (!$file_descriptor) {
            return false;
        }

        fwrite($file_descriptor, $_data, $image['size']);
        fclose($file_descriptor);
        @chmod($image['path'], DEFAULT_FILE_PERMISSIONS);

        return $image;
    }
}
