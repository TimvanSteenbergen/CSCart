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

namespace Tygh;

use Tygh\Exceptions\DeveloperException;

class Cdn
{
    private static $_instance = null;

    /**
     * Gets CDN object instance
     *
     * @return Cdn CDN object instance
     */
    public static function instance()
    {
        if (empty(self::$_instance)) {
            $backend = Registry::get('config.cdn_backend');

            if (empty($backend)) {
                throw new DeveloperException('CDN: undefined CDN backend');
            }

            $options = Settings::instance()->getValue('cdn', '');
            $options = !empty($options) ? unserialize($options) : array();

            $class = '\\Tygh\\Backend\\Cdn\\' . ucfirst($backend);
            self::$_instance = new $class($options);
        }

        return self::$_instance;
    }
}
