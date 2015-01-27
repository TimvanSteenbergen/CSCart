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

namespace Twigmo\Core;

use Tygh\Registry;
use Twigmo\Core\Functions\Image\TwigmoImage;

class TwigmoSettings
{
    const REGISTRY_PATH = 'runtime.twigmo';

    /**
     * @param string $path Path to settings in the 'runtime.twigmo'
     * @return mixed
     */
    public static function get($path = '')
    {
        if ($path) {
            $path = '.' . ltrim($path, '.');
        }
        $path = self::REGISTRY_PATH . $path;
        $value = Registry::get($path);
        if (is_null($value)) {
            // To avoid empty value appearing in the registry we have to delete it
            Registry::del($path);
        }
        return $value;
    }

    public static function set($settings)
    {
        if (empty($settings)) {
            return;
        }
        // Save admin settings
        if (isset($settings['admin_connection'])) {
            self::_setStoreSettings($settings['admin_connection'], 'A');
            unset($settings['admin_connection']);
        }
        // Save customer settings
        if (isset($settings['customer_connections'])) {
            $customer_connections = $settings['customer_connections'];
            if (empty($customer_connections)) {
                self::_setStoreSettings(array(), 'C');
            } else {
                foreach ($customer_connections as $company_id => $customer_connection) {
                    $customer_connection['company_id'] = $company_id;
                    self::_setStoreSettings($customer_connection, 'C');
                }
            }
            unset($settings['customer_connections']);
        }
        self::_setCommonSettings($settings);
        // And update runtime
        self::moveToRuntime();
    }

    /**
     * Move settings from the database to the registry.
     * @param array $default_settings
     */
    public static function moveToRuntime($default_settings = array())
    {
        $in_runtime = self::get();
        if (!$in_runtime) {
            $in_runtime = array();
        }
        $settings = array_merge($default_settings, $in_runtime, self::_getFromDB());
        // Merge current company's settings to the root
        $company_id = fn_twg_get_current_company_id();
        if (!empty($settings['customer_connections'][$company_id])) {
            $settings = array_merge($settings, $settings['customer_connections'][$company_id]);
        }
        Registry::set(self::REGISTRY_PATH, $settings);
        // To avoid caching set for each store
        Registry::set(self::REGISTRY_PATH . '.customer_connections', $settings['customer_connections']);
        foreach ($settings['customer_connections'] as $company_id => $connection) {
            Registry::set(self::REGISTRY_PATH . ".customer_connections.$company_id", $connection);
        }
    }

    public static function dbIsInited()
    {
        return !!db_get_field("SHOW TABLES LIKE '?:twigmo_settings'");
    }

    private static function _getFromDB()
    {
        $settings = db_get_hash_single_array('SELECT * FROM ?:twigmo_settings', array('name', 'value'));
        $store_sql = 'SELECT * FROM ?:twigmo_stores WHERE type = ?s';
        $settings['admin_connection'] = db_get_row($store_sql, 'A');
        $settings['customer_connections'] = db_get_hash_array($store_sql, 'company_id', 'C');
        return $settings;
    }

    private static function _setStoreSettings($store, $type = 'C')
    {
        if (empty($store)) {
            db_query('DELETE FROM ?:twigmo_stores WHERE type = ?s', $type);
            return;
        }
        if (!isset($store['company_id'])) {
            $store['company_id'] = 0;
        }
        $condition = db_quote(' WHERE company_id = ?i AND type = ?s ', $store['company_id'], $type);
        $is_old_store = db_get_field("SELECT count(*) FROM ?:twigmo_stores $condition", $store['company_id'], $type);
        $store = array_merge($store, self::_saveUploadedLogos());
        $store['type'] = $type;
        if ($is_old_store) {
            db_query("UPDATE ?:twigmo_stores SET ?u $condition", $store);
        } else {
            $store['repo_revision'] = TIME;
            db_query('INSERT INTO ?:twigmo_stores ?e', $store);
        }
    }

    private static function _setCommonSettings($settings)
    {
        if (empty($settings)) {
            return;
        }
        foreach ($settings as $name => $value) {
            db_query('REPLACE INTO ?:twigmo_settings ?e', array('name' => $name, 'value' => $value));
        }
    }

    /**
     * Save uploaded logo and favicon and return array of their urls
     * @return Array of Strings
     */
    private static function _saveUploadedLogos()
    {
        $logo_names = array('logo', 'favicon');
        $options = array();

        foreach ($logo_names as $logo_name) {
            $pair_ids = fn_attach_image_pairs($logo_name, 'twg_logos');
            if (!empty($pair_ids)) {
                $image_id = TwigmoImage::getImageId(array('pair_id' => reset($pair_ids), 'object_type' => 'twg_logos'));
                $image_data = fn_get_image($image_id, 'twg_logos');
                $options[$logo_name . '_url'] = $image_data['http_image_path'];
            }
        }

        return $options;
    }
}
