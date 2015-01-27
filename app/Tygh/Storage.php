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

class Storage
{
    private static $_instance = array();

    /**
     * Gets storage object instance
     *
     * @param  string  $type    type of storage
     * @param  array   $options options
     * @return Storage storage object instance
     */
    public static function instance($type, $options = array())
    {
        $options = empty($options) ? Registry::get('runtime.storage') : $options;
        $storage = $options['storage'];

        if (empty($storage)) {
            throw new DeveloperException('Storage: undefined storage backend');
        }

        if (!Registry::get('config.storage.' . $type)) {
            throw new DeveloperException('Storage: undefined storage type - ' . $type);
        }

        if (empty(self::$_instance[$storage])) {
            $class = '\\Tygh\\Backend\\Storage\\' . ucfirst($storage);
            self::$_instance[$storage] = new $class();
        }

        self::$_instance[$storage]->options = fn_array_merge($options, Registry::get('config.storage.' . $type));
        self::$_instance[$storage]->type = $type;

        return self::$_instance[$storage];
    }
}
