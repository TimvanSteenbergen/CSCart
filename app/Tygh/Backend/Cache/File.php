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

namespace Tygh\Backend\Cache;

use Tygh\Exceptions\PermissionsException;
use Tygh\Registry;

class File extends ABackend
{
    public function set($name, $data, $condition, $cache_level = NULL)
    {
        if (!empty($data)) {
            fn_put_contents($this->_mapTags($name) . '/' . $cache_level, serialize(array(
                'data' => $data,
                'expiry' => $cache_level == Registry::cacheLevel('time') ? TIME + $condition : 0
            )));
        }
    }

    public function get($name, $cache_level = NULL)
    {
        $fname = $this->_mapTags($name) . '/' . $cache_level;

        if (!empty($name) && is_readable($fname)) {
            $_cache_data = @unserialize(fn_get_contents($fname));
            if (!empty($_cache_data) && ($cache_level != Registry::cacheLevel('time') || ($cache_level == Registry::cacheLevel('time') && $_cache_data['expiry'] > TIME))) {
                return array($_cache_data['data']);

            } else { // clean up the cache
                fn_rm($fname);
            }
        }

        return false;
    }

    public function clear($tags)
    {
        foreach ($tags as $tag) {
            fn_rm($this->_mapTags($tag, 0), true); // cleanup tags for root and all companies
        }

        return true;
    }

    public function getHandlers()
    {
        $ch = fn_get_contents($this->_mapTags($this->_handlers_name, 0));

        return !empty($ch) ? @unserialize($ch) : array();
    }

    public function saveHandlers($cache_handlers)
    {
        return fn_put_contents($this->_mapTags($this->_handlers_name, 0), serialize($cache_handlers));
    }

    public function cleanup()
    {
        fn_rm($this->_mapTags('', 0), false);

        return true;
    }

    public function acquireLock($key, $cache_level)
    {
        $fname = $this->_mapTags('locks') . '/' . $key . $cache_level;

        if (file_exists($fname)) {
            $ttl = fn_get_contents($fname);
            if ($ttl < time()) { // lock expired

                return fn_put_contents($fname, time() + Registry::LOCK_EXPIRY);
            }
        } else {
            return fn_put_contents($fname, time() + Registry::LOCK_EXPIRY);
        }

        return false;
    }

    public function releaseLock($key, $cache_level)
    {
        $fname = $this->_mapTags('locks') . '/' . $key . $cache_level;

        return fn_rm($fname);
    }

    public function __construct($config)
    {
        $this->_config = array(
            'saas_uid' => !empty($config['saas_uid']) ? $config['saas_uid'] : null,
            'dir_cache' => $config['dir']['cache_registry']
        );

        if (fn_mkdir($this->_mapTags('')) == false) {
            throw new PermissionsException('Cache: "' . $this->_mapTags('') . '" directory is not writable');
        }

        parent::__construct($config);

        return true;
    }

    private function _mapTags($tags, $company_id = null)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
            $return_one = true;
        }

        $company_id = !is_null($company_id) ? $company_id : $this->_company_id;
        $suffix = !empty($company_id) ? ('/' . $company_id) : '';

        foreach ($tags as $k => $v) {
            $tags[$k] = $this->_config['dir_cache'] . (!empty($this->_config['saas_uid']) ? $this->_config['saas_uid'] . '/' : '') . $v . $suffix;
        }

        return !empty($return_one) ? array_shift($tags) : $tags;
    }

}
