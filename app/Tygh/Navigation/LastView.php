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

namespace Tygh\Navigation;

class LastView
{
    private static $_instance = null;

    /**
     * Gets last view object instance
     *
     * @param  string   $area Area identifier
     * @return LastView object instance
     */
    public static function instance($area = AREA)
    {
        if (!isset(self::$_instance)) {
            $class = '\\Tygh\\Navigation\\LastView\\' . ucfirst(fn_get_area_name($area));
            self::$_instance = new $class();
        }

        return self::$_instance;
    }
}
