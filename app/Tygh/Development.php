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

class Development
{
    private static $modes = null;

    /**
     * Checks if mode is enabled
     * @param  string  $mode mode to check
     * @return boolean true if enabled, false - otherwise
     */
    public static function isEnabled($mode)
    {
        self::read();

        return !empty(self::$modes[$mode]);
    }

    /**
     * Enables development mode
     * @param stirng $mode mode to enable
     */
    public static function enable($mode)
    {
        self::read();
        self::$modes[$mode] = true;
        self::save();
    }

    /**
     * Disables development mode
     * @param string $mode mode to disable
     */
    public static function disable($mode)
    {
        self::read();
        unset(self::$modes[$mode]);
        self::save();
    }

    /**
     * Gets development mode settings
     * @return array enabled modes
     */
    public static function get()
    {
        self::read();

        return self::$modes;
    }

    /**
     * Saves development mode settings
     */
    private static function save()
    {
        fn_set_storage_data('dev_mode', serialize(self::$modes));
    }

    /**
     * Reads development mode settings
     */
    private static function read()
    {
        if (is_null(self::$modes)) {
            $modes = fn_get_storage_data('dev_mode');
            if (!empty($modes)) {
                $modes = unserialize($modes);
            } else {
                $modes = array();
            }

            self::$modes = $modes;
        }
    }

    /**
     * Displays stub when store is closed
     * @param array $placeholders placeholders
     * @param string $append additional text
     */
    public static function showStub($placeholders = array(), $append = '')
    {
        if (empty($placeholders)) {
            $placeholders = array(
                '[title]' => __('store_closed'),
                '[banner]' =>  __('store_closed_banner'),
                '[message]' => __('text_store_closed')
            );
        }

        if (!headers_sent()) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: 300');
        }

        $content = file_get_contents(DIR_ROOT . '/store_closed.html');
        echo(str_replace(array_keys($placeholders), $placeholders, $content));

        if ($append) {
            echo($append);
        }

        exit;
    }    
}
