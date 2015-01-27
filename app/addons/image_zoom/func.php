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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Check detiled image sizes ration
 *
 * @param array $image_data Image data
 * @param array $images Array with initial images
 * @param $image_width Result image width
 * @param $image_height Result image height
 * @return boolean Always true
 */
function fn_image_zoom_image_to_display_post(&$image_data, &$images, &$image_width, &$image_height)
{
    if (!empty($images['detailed']) && !empty($image_data['detailed_image_path'])) {

        $precision = 100;

        $ratio_detailed = round($images['detailed']['image_x']/$images['detailed']['image_y'] * $precision)/$precision;
        $ratio_original = round($image_data['width']/$image_data['height'] * $precision)/$precision;

        if ($ratio_detailed != $ratio_original) {
            if ($ratio_detailed < $ratio_original) {
                $new_x = ceil($images['detailed']['image_y']/$image_data['height'] * $image_data['width']);
                $new_y = $images['detailed']['image_y'];
            } else {
                $new_y = ceil($images['detailed']['image_x']/$image_data['width'] * $image_data['height']);
                $new_x = $images['detailed']['image_x'];
            }

            $image_data['detailed_image_path'] = fn_generate_thumbnail($images['detailed']['relative_path'], $new_x, $new_y, false);
        }
    }

    return true;
}
