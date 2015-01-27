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
use Tygh\Settings;

function fn_settings_actions_addons_hidpi(&$new_value, $old_value)
{
    Storage::instance('images')->deleteDir('thumbnails');

    if ($new_value == 'A') {
        $formats = fn_check_gd_formats();

        // Set thumbnail generation format to png to improve quality
        if (!empty($formats['png'])) {
            Settings::instance()->updateValue('convert_to', 'png', 'Thumbnails');
        }
    }
}
