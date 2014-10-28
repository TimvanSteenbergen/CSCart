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

use Tygh\Registry;
use Tygh\Debugger;

class Redis extends ABackend
{
    /**
     * @var Redis
     */
    private $r;

    public function set($name, $data, $condition, $cache_level = NULL)
    {
        if (!empty($data)) {
            $key = $this->_mapTags($name);
            $this->r->hSet($key, $cache_level, array(
                'data' => $data,
                'expiry' => $cache_level == Registry::cacheLevel('time') ? TIME + $condition : 0
            ));

            if (!empty($this->_config['global_ttl'])) {
                $this->r->setTimeout($key, $this->_config['global_ttl']);
            }
        }
    }

    public function get($name, $cache_level = NULL)
    {
        $data = $this->r->hGet($this->_mapTags($name), $cache_level);

        if (!empty($data)) {
            if (!empty($data) && ($cache_level != Registry::cacheLevel('time') || ($cache_level == Registry::cacheLevel('time') && $data['expiry'] > TIME))) {
                return array($data['data']);

            } else { // clean up the cache
                $this->r->del($this->_mapTags($name));
            }
        }

        return false;
    }

    public function clear($tags)
    {
        // clear method calls in shutdown function, so redis object can be destructed already
        if (empty($this->r)) {
            $this->_connect($this->_config);
        }

        if (!empty($tags)) {
            // we have to get all keys, because tags may have company suffix
            $tags = $this->_mapTags($tags, 0);
            $all_keys = $this->r->keys($this->_mapTags('', 0) . '*');
            $mapped_tags = array();
            foreach ($all_keys as $key) {
                foreach ($tags as $tag) {
                    if (strpos($key, $tag) === 0) {
                        $mapped_tags[] = $key;
                    }
                }
            }

            $this->r->del($mapped_tags);
        }

        return true;
    }

    public function saveHandlers($cache_handlers)
    {
        $this->r->hmSet($this->_mapTags($this->_handlers_name, 0), $cache_handlers);

        return true;
    }

    public function getHandlers()
    {
        return $this->r->hGetAll($this->_mapTags($this->_handlers_name, 0));
    }

    public function cleanup()
    {
        $keys = $this->r->keys($this->_mapTags('', 0) . '*');

        $this->r->del($keys);

        return true;
    }

    public function acquireLock($key, $cache_level)
    {
        $name = $key . $cache_level;

        if ($this->r->hsetnx($this->_mapTags('_locks'), $name, time() + Registry::LOCK_EXPIRY)) {
            return true;
        }

        if ($ttl = $this->r->hget($this->_mapTags('_locks'), $name)) {
            if ($ttl < time()) { // lock expired
                $this->r->hset($this->_mapTags('_locks'), $name, time() + Registry::LOCK_EXPIRY);

                return true;
            }
        }

        return false;
    }

    public function releaseLock($key, $cache_level)
    {
        $this->r->hdel($this->_mapTags('_locks'), $key . $cache_level);
    }

    public function __construct($config)
    {
        $this->_config = array(
            'redis_server' => $config['cache_redis_server'],
            'saas_uid' => !empty($config['saas_uid']) ? $config['saas_uid'] : null,
            'global_ttl' => !empty($config['cache_redis_global_ttl']) ? $config['cache_redis_global_ttl'] : 0,
        );

        parent::__construct($config);

        if ($this->_connect($config)) {
            return true;
        }

        return false;
    }

    private function _connect()
    {
        $this->r = new \Redis();

        Debugger::checkpoint('Cache: before redis connect');
        if ($this->r->connect($this->_config['redis_server']) == true) {
            $this->r->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
            Debugger::checkpoint('Cache: after redis connect');

            return true;
        }

        return false;
    }

    private function _mapTags($tags, $company_id = null)
    {
        if (!is_array($tags)) {
            $tags = array($tags);
            $return_one = true;
        }

        $company_id = !is_null($company_id) ? $company_id : $this->_company_id;
        $suffix = !empty($company_id) ? (':' . $company_id) : '';

        foreach ($tags as $k => $v) {
            $tags[$k] = 'cache:' . (!empty($this->_config['saas_uid']) ? $this->_config['saas_uid'] . ':' : '') . $v . $suffix;
        }

        return !empty($return_one) ? array_shift($tags) : $tags;
    }
}
