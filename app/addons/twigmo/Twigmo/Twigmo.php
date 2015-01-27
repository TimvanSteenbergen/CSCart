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

namespace Twigmo;

class Twigmo
{
    /*
     * Check environment for correct addon work
     * @return array
     */
    public static function checkRequirements()
    {
        $errors = array();
        if (!function_exists('hash_hmac')){
            $errors[] = str_replace('[php_module_name]', 'Hash', __('twgadmin_phpmod_required'));
        }
        return $errors;
    }
}
