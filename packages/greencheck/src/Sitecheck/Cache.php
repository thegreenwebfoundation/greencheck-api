<?php

namespace TGWF\Greencheck\Sitecheck;

use TGWF\Greencheck\Cache\DisabledCache;

/**
 * Sitecheck class.
 *
 * The sitecheck handles all actions with regard to the Green Web Foundation greencheck.
 *
 * Flow :
 * - Check the cached records for an url, if found return
 * - Check the customer records for an url, if found return
 * - Check the ip records for an url, if found return
 * - Check the as records for an url, if found return
 * - None found, then return url = grey
 *
 * @author Arend-Jan Tetteroo <aj@arendjantetteroo.nl>
 */
class Cache
{
    /**
     * @var array
     */
    protected $_cache = null;

    /**
     * @var array
     */
    protected $_config = null;

    /**
     * Cache is enabled by default.
     *
     * @var bool
     */
    protected $_disable = false;

    /**
     * Construct the sitecheck.
     *
     * @param array $config [description]
     */
    public function __construct($config)
    {
        $this->_config = $config;

        if (false == $config['greencheck']['cache']) {
            $this->disableCache();
        }

        // Setup the cache
        $this->setCache('default');
    }

    public function getTTL($key = 'default')
    {
        $config = $this->_config;
        if ('default' != $key && isset($config['greencheck'][$key])) {
            $lifetime = $config['greencheck'][$key]['cachetime'];
        } else {
            $lifetime = $config['greencheck']['cachetime'];
        }

        return $lifetime;
    }

    public function getCacheEnabled($key = 'default')
    {
        if ($this->isDisabled()) {
            return false;
        }

        $config = $this->_config;
        if ('default' != $key && isset($config['greencheck'][$key])) {
            $caching = $config['greencheck'][$key]['cache'];
        } else {
            $caching = $config['greencheck']['cache'];
        }

        return $caching;
    }

    public function getCacheType()
    {
        $config = $this->_config;
        if (isset($config['greencheck']['cachetype'])) {
            $cachetype = $config['greencheck']['cachetype'];
        } else {
            $cachetype = 'apc';
        }

        return $cachetype;
    }

    public function getCacheDriver($cachetype)
    {
        if ('memcache' == $cachetype) {
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1', 11211);

            $cache = new \Doctrine\Common\Cache\MemcacheCache();
            $cache->setMemcache($memcache);
        } elseif ('redis' == $cachetype) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);

            $cache = new \Doctrine\Common\Cache\RedisCache();
            $cache->setRedis($redis);
        } else {
            $cache = new \Doctrine\Common\Cache\ApcCache();
        }

        $cache->setNamespace('tgwf_greencheck');

        return $cache;
    }

    /**
     * Setup the cache functions.
     */
    private function setupCache($key = 'default')
    {
        if ($this->isDisabled() || false == $this->getCacheEnabled($key)) {
            $cache = new DisabledCache();

            return $cache;
        }

        $cache = $this->getCacheDriver($this->getCacheType());

        return $cache;
    }

    public function disableCache()
    {
        $this->_disable = true;
    }

    public function isDisabled()
    {
        return $this->_disable;
    }

    public function resetCache($key)
    {
        if (isset($this->_cache[$key])) {
            $cache = $this->_cache[$key];
            // clear all
            $cache->deleteAll();
        }
    }

    public function setCache($key, $cache = null)
    {
        if (!is_null($cache)) {
            $this->_cache[$key] = $cache;
        } else {
            $this->_cache[$key] = $this->setupCache($key);
        }
    }

    public function getCache($key = 'default')
    {
        if (!isset($this->_cache[$key])) {
            $this->setCache($key);
        }

        if ($this->_disable) {
            return new DisabledCache();
        }

        return $this->_cache[$key];
    }

    public function setItem($cache, $key, $data)
    {
        $ttl = $this->getTTL($cache);
        $this->getCache($cache)->save(sha1($key), $data, $ttl);
    }
}
