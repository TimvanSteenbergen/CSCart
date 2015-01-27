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

class Registry
{
    private static $_storage = array();
    private static $_cached_keys = array();
    private static $_changed_tables = array();
    private static $_storage_cache = array();
    private static $_cache_levels = array();
    private static $_cache_handlers = array();
    private static $_cache_handlers_are_updated = false;

    private static $_cache = null;
    private static $_locks = array();

    const LOCK_WAIT = 100000; // mircoseconds
    const LOCK_EXPIRY = 20; // seconds
    const NOT_FOUND = '/#not found#/';

    /**
     * Puts variable to registry
     *
     * @param string  $key      key name
     * @param mixed   $value    key value
     * @param boolean $no_cache if set to true, data won't be cache even if it's registered in the cache
     *
     * @return boolean always true
     */
    public static function set($key, $value, $no_cache = false)
    {
        if (strpos($key, '.') !== false) {
            list($_key) = explode('.', $key);
        } else {
            $_key = $key;
        }

        $var = & self::_varByKey('create', $key);
        $var = $value;

        if ($no_cache == false && isset(self::$_cached_keys[$_key]) && self::$_cached_keys[$_key]['track'] == false) { // save cache immediatelly
            $_var = (strpos($key, '.') !== false) ? self::get($_key) : $value;

            self::_saveCache($_key, $_var);
            unset(self::$_cached_keys[$_key]);
        }

        return true;
    }

    /**
     * Gets variable from registry (value can be returned by reference)
     *
     * @param string $key key name
     *
     * @return mixed key value
     */
    public static function get($key)
    {
        $val = self::_varByKey('get', $key);

        return ($val !== self::NOT_FOUND) ? $val : null;
    }

    /**
     * Pushes data to array
     *
     * @param string $key key name
     * @paramN mixed values to push to the key value
     *
     * @return boolean always true
     */
    public static function push()
    {
        $args = func_get_args();
        $key = array_shift($args);

        $data = self::get($key);
        if (!is_array($data)) {
            $data = array();
        }

        $data =	array_merge($data, $args);

        return self::set($key, $data);
    }

    /**
     * Deletes key from registry
     *
     * @param string $key key name
     *
     * @return boolean true if key found, false - otherwise
     */
    public static function del($key)
    {
        if (self::_varByKey('delete', $key) === self::NOT_FOUND) {
            return false;
        }

        return true;
    }

    /**
     * Private: performs key action
     *
     * @param string $action key action (get, create, delete)
     * @param string $key    key name
     *
     * @return mixed key value
     */
    private static function & _varByKey($action, $key)
    {
        if ($action == 'get' && isset(self::$_storage_cache[$key])) {
            return self::$_storage_cache[$key];
        }

        $not_found = self::NOT_FOUND;
        $parts = (strpos($key, '.') !== false) ? explode('.', $key) : array($key);
        $piece = & self::$_storage;
        $length = sizeof($parts);
        $i = 0;

        foreach ($parts as $part) {
            $i++;

            if ($action == 'create' && !isset($piece[$part])) {
                $piece[$part] = array();
            }

            if (is_array($piece) && array_key_exists($part, $piece)) { // isset does not return true on null values

                if ($action == 'delete' && $i == $length) {
                    unset($piece[$part]);
                    unset(self::$_storage_cache[$key]);
                    $not_found = true;

                    return $not_found;
                }

                $piece = & $piece[$part];

                continue;
            }

            return $not_found;
        }

        // If we creating new key, cleanup cached key children
        if ($action == 'create') {
            foreach (self::$_storage_cache as $k => $v) {
                if (strpos($k, $key . '.') === 0) {
                    unset(self::$_storage_cache[$k]);
                }
            }
        }

        // cache complex keys only
        if ($length > 1) {
            self::$_storage_cache[$key] = & $piece;

            return self::$_storage_cache[$key];
        }

        return $piece;
    }

    /**
     * Conditional get, returns default value if key does not exist in registry
     *
     * @param string $key     key name
     * @param mixed  $default default value
     *
     * @return mixed key value if exist, default value otherwise
     */
    public static function ifGet($key, $default)
    {
        $var = self::get($key);

        return !empty($var) ? $var : $default;
    }

    /**
     * Checks if key exists in the registry
     *
     * @param string $key key name
     *
     * @return boolean true if key exists, false otherwise
     */
    public static function isExist($key)
    {
        $var = self::_varByKey('get', $key);

        return $var !== self::NOT_FOUND;
    }

    /**
     * Marks table as changed
     *
     * @param string $table table name
     *
     * @return boolean always true
     */
    public static function setChangedTables($table)
    {
        self::$_changed_tables[$table] = true;

        return true;
    }

    /**
     * Registers variable in the cache
     *
     * @param string $key         key name
     * @param mixed  $condition   cache reset condition - array with table names of expiration time (int)
     * @param string $cache_level indicates the cache dependencies on controller, language, user group, etc
     * @param bool   $track       if set to true, cache data will be collection during script execution and saved when it finished
     *
     * @return boolean true if data is cached and valid, false - otherwise
     */
    public static function registerCache($key, $condition, $cache_level = NULL, $track = false)
    {
        if (empty(self::$_cache)) {
            self::cacheInit();
        }

        if (empty(self::$_cached_keys[$key])) {
            self::$_cached_keys[$key] = array(
                'condition' => $condition,
                'cache_level' => $cache_level,
                'track' => $track,
                'hash' => ''
            );

            if (!self::isExist($key) && ($val = self::_getCache($key, $cache_level)) !== NULL) {
                self::set($key, $val, true);

                // Get hash of original value for tracked data
                if ($track == true) {
                    self::$_cached_keys[$key]['hash'] = md5(serialize($val));
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Inits cache backend
     *
     * @return boolean always true
     */
    public static function cacheInit()
    {
        if (empty(self::$_cache)) {
            $_cache_class = self::ifGet('config.cache_backend', 'file');
            $_cache_class = '\\Tygh\\Backend\\Cache\\' . ucfirst($_cache_class);

            self::$_cache = new $_cache_class(self::get('config'));
            self::$_cache_handlers = self::$_cache->getHandlers();
        }

        return true;
    }

    /**
     * Gets cached data
     *
     * @param string $key         key name
     * @param string $cache_level indicates the cache dependencies on controller, language, user group, etc
     *
     * @return mixed cached data if exist, NULL otherwise
     */
    private static function _getCache($key, $cache_level = NULL)
    {
        $time_start = microtime(true);
        $data = self::$_cache->get($key, $cache_level);
        Debugger::set_cache_query($key . '::' . $cache_level, microtime(true) - $time_start);

        return (($data !== false) && (!empty($data[0]))) ? $data[0] : NULL;
    }

    /**
     * Assigns database tables to cache key for future cache update
     * @param string $key         key name
     * @param array  $condition   tables list
     * @param string $cache_level cache level
     */
    private static function _updateHandlers($key, $condition, $cache_level)
    {
        if ($cache_level != self::cacheLevel('time')) {
            foreach ($condition as $table) {
                if (empty(self::$_cache_handlers[$table])) {
                    self::$_cache_handlers[$table] = array();
                }

                self::$_cache_handlers[$table][$key] = true;
                self::$_cache_handlers_are_updated = true;
            }
        }
    }

    /**
     * Acquires key lock
     * @param  string  $key         key name
     * @param  string  $cache_level cache level
     * @return boolean true on success, false on failure
     */
    private static function _acquireLock($key, $cache_level)
    {
        if (empty(self::$_locks[$key])) {
            if (self::$_cache->acquireLock($key, $cache_level)) {
                self::$_locks[$key] = true;
            }
        }

        if (!empty(self::$_locks[$key])) {
            return true;
        }

        return false;
    }

    /**
     * Saves data to cache
     * @param string $key key name
     * @param mixed  $val value
     */
    private static function _saveCache($key, $val)
    {
        if (empty(self::$_cached_keys[$key]['hash']) || self::$_cached_keys[$key]['hash'] != md5(serialize(self::$_storage[$key]))) {
            self::$_cache->set($key, $val, self::$_cached_keys[$key]['condition'], self::$_cached_keys[$key]['cache_level']);
            self::_updateHandlers($key, self::$_cached_keys[$key]['condition'], self::$_cached_keys[$key]['cache_level']);
        }
    }

    /**
     * Saves tracked cached data and clears expired cache
     *
     * @return boolean true if data saved, false if no caches defined
     */
    public static function save()
    {

        if (empty(self::$_cache)) {
            return false;
        }

        foreach (self::$_cached_keys as $key => $arg) {
            if (isset(self::$_storage[$key]) && $arg['track'] == true) {
                self::_saveCache($key, self::$_storage[$key]);
            }
        }
        self::$_cached_keys = array();

        if (self::$_cache_handlers_are_updated == true) {
            self::$_cache->saveHandlers(self::$_cache_handlers);
            self::$_cache_handlers_are_updated = false;
        }

        // Get tags to clear expired cache
        if (!empty(self::$_changed_tables)) {
            $tags = array();
            foreach (self::$_changed_tables as $table => $flag) {
                if (!empty(self::$_cache_handlers[$table])) {
                    $tags = array_merge($tags, array_keys(self::$_cache_handlers[$table]));
                }
            }

            foreach ($tags as $tag) {
                self::del($tag);
            }

            self::$_cache->clear($tags);
            self::$_changed_tables = array();
        }

        return true;
    }

    /**
     * Cleans up cache data
     *
     * @return boolean always true
     */
    public static function cleanup()
    {
        if (empty(self::$_cache)) {
            self::cacheInit();
        }

        return self::$_cache->cleanup();
    }

    /**
     * Generates cache level value for key
     *
     * @param $id cache level name
     * @return string cache level value
     */
    public static function cacheLevel($id)
    {
        if (!isset(self::$_cache_levels[$id])) {
            if ($id == 'time') {
                $key = 'time';
            } elseif ($id == 'static') {
                $key = 'cache_' . ACCOUNT_TYPE;
            } elseif ($id == 'day') {
                $key = date('z', TIME);
            } elseif ($id == 'locale') {
                $key = (defined('CART_LOCALIZATION') ? (CART_LOCALIZATION . '_') : '') . CART_LANGUAGE . '_' . CART_SECONDARY_CURRENCY;
            } elseif ($id == 'dispatch') {
                $key = AREA . '_' . $_SERVER['REQUEST_METHOD'] . '_' . str_replace('.', '_', $_REQUEST['dispatch']) . '_' . (defined('CART_LOCALIZATION') ? (CART_LOCALIZATION . '_') : '') . CART_LANGUAGE . '_' . CART_SECONDARY_CURRENCY;
            } elseif ($id == 'user') {
                $key =  AREA . '_' . $_SERVER['REQUEST_METHOD'] . '_' . str_replace('.', '_', $_REQUEST['dispatch']) . '.' . (!empty($_SESSION['auth']['usergroup_ids']) ? implode('_', $_SESSION['auth']['usergroup_ids']) : '') . '.' . (defined('CART_LOCALIZATION') ? (CART_LOCALIZATION . '_') : '') . CART_LANGUAGE . '.' . CART_SECONDARY_CURRENCY;
            } elseif ($id == 'locale_auth') {
                $key = AREA . '_' . $_SERVER['REQUEST_METHOD'] . '_' . (!empty($_SESSION['auth']['user_id']) ? 1 : 0) . '.' . (!empty($_SESSION['auth']['usergroup_ids']) ? implode('_', $_SESSION['auth']['usergroup_ids']) : '') . (defined('CART_LOCALIZATION') ? (CART_LOCALIZATION . '_') : '') . CART_LANGUAGE . '.' . CART_SECONDARY_CURRENCY;
            } elseif ($id == 'html_blocks') {
                $promotion_condition =  (!empty($_SESSION['auth']['user_id']) && db_get_field("SELECT count(*) FROM ?:promotions WHERE status = 'A' AND zone = 'catalog' AND users_conditions_hash LIKE ?l", "%," . $_SESSION['auth']['user_id'] . ",%") > 0)? $_SESSION['auth']['user_id'] : '';
                $https_condition = defined('HTTPS') ? '__https' : '';

                $key = (defined('CART_LOCALIZATION') ? (CART_LOCALIZATION . '__') : '') . CART_LANGUAGE . '__' . self::cacheLevel('day') . '__' . (!empty($_SESSION['auth']['usergroup_ids'])? implode('_', $_SESSION['auth']['usergroup_ids']) : '') . '__' . $promotion_condition . $https_condition;
            }

            if (!isset($key)) {
                throw new DeveloperException('Registry: undefined cache level');
            }

            self::$_cache_levels[$id] = $key;
        }

        return self::$_cache_levels[$id];
    }

    /**
     * Clears defined cache levels to redefine them again later
     */
    public static function clearCacheLevels()
    {
        self::$_cache_levels = array();
    }
}
