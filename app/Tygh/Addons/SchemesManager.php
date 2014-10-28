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

namespace Tygh\Addons;
use Tygh\Registry;

class SchemesManager
{
    public static $schemas;

    /**
     * Creates and returns XmlScheme object for addon
     *
     * @param  string    $addon_id Addon name
     * @param  string    $path     Path to addons
     * @return XmlScheme object
     */
    public static function getScheme($addon_id, $path = '')
    {
        if (empty($path)) {
            $path = Registry::get('config.dir.addons');
        }

        libxml_use_internal_errors(true);

        if (!isset (self::$schemas[$addon_id])) {
            $_xml = self::readXml($path . $addon_id . '/addon.xml');
            if ($_xml !== FALSE) {
                $versions = self::getVersionDefinition();
                $version = (isset($_xml['scheme'])) ? (string) $_xml['scheme'] : '1.0';
                self::$schemas[$addon_id] = new $versions[$version]($_xml);
            } else {
                $errors = libxml_get_errors();

                $text_errors = array();
                foreach ($errors as $error) {
                    $text_errors[] = self::displayXmlError($error, $_xml);
                }

                libxml_clear_errors();
                if (!empty($text_errors)) {
                    fn_set_notification('E', __('xml_error'), '<br/>' . implode('<br/>' , $text_errors));
                }

                return false;
            }
        }

        return self::$schemas[$addon_id];
    }

    /**
     * Loads xml
     * @param $filename
     * @return bool
     */
    private static function readXml($filename)
    {
        if (file_exists($filename)) {
            return simplexml_load_file($filename);
        }

        return false;
    }

    /**
     * Returns the scheme in which a class processing any certain xml scheme version is defined.
     * @static
     * @return array
     */
    private static function getVersionDefinition()
    {
        return array(
            '1.0' => 'Tygh\\Addons\\XmlScheme1',
            '2.0' => 'Tygh\\Addons\\XmlScheme2',
            '3.0' => 'Tygh\\Addons\\XmlScheme3',
        );
    }

    /**
     * Returns list of addons that will not be worked correctly without it
     * @static
     * @param $addon_id
     * @param $lang_code
     * @return array
     */
    public static function getInstallDependencies($addon_id, $lang_code = CART_LANGUAGE)
    {
        $scheme = self::getScheme($addon_id);
        $dependencies = array();

        if ($scheme !== false) {
            $addons = $scheme->getDependencies();
            $dependencies = self::getNames($addons, false, $lang_code);
        }

        return $dependencies;
    }

    /**
     * Returns list of addons that will not be worked correctly without it
     * @static
     * @param $addon_id
     * @param $lang_code
     * @return array
     */
    public static function getUninstallDependencies($addon_id, $lang_code = CART_LANGUAGE)
    {
        $addons = db_get_fields('SELECT addon FROM ?:addons WHERE dependencies LIKE ?l', '%' . $addon_id . '%');
        $dependencies = self::getNames($addons, true, $lang_code);

        return $dependencies;
    }

    /**
     * Convert addon's ids list to to array of addon names as addon_id => addon_name;
     * @static
     * @param $addons array of addon id's
     * @param $lang_code 2digits lang code
     * @return array
     */
    public static function getNames($addons, $with_installed = true, $lang_code = CART_LANGUAGE)
    {
        $addon_names = Array();

        foreach ($addons as $addon_id) {
            if (!empty($addon_id) && (Registry::get('addons.' . $addon_id) == null || $with_installed)) {
                $scheme = self::getScheme($addon_id);
                if ($scheme !== false) {
                    $addon_names[$addon_id] = $scheme->getName($lang_code);
                }
            }
        }

        return $addon_names;
    }

    private static function displayXmlError($error, $xml)
    {
        $return  = $xml[$error->line - 1] . "\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= '<b>'. __('warning') . " $error->code:</b> ";
                break;
             case LIBXML_ERR_ERROR:
                $return .= '<b>'. __('error') . " $error->code:</b> ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= '<b>'. __('error') . " $error->code:</b> ";
                break;
        }

        $return .= trim($error->message) . '<br/>  <b>' . __('line') . "</b>: $error->line" . '<br/>  <b>' . __('column') . "</b>: $error->column";

        if ($error->file) {
            $return .= '<br/> <b>' . $error->file . '</b>';
        }

        return "$return<br/>";
    }
}
