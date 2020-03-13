<?php

namespace TGWF\Greencheck\Sitecheck;

use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\RedisCache;
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
    protected $cache;

    /**
     * @var array
     */
    protected $config;

    /**
     * Cache is enabled by default.
     *
     * @var bool
     */
    protected $disabled = false;

    /**
     * Construct the sitecheck.
     *
     * @param array $config [description]
     */
    public function __construct($config)
    {
        $this->config = $config;

        if (false == $this->config['greencheck']['cache']) {
            $this->disableCache();
        }

        // Setup the cache
        $this->setCache('default');
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getTTL($key = 'default')
    {
        if ('default' !== $key && isset($this->config['greencheck'][$key])) {
            return $this->config['greencheck'][$key]['cachetime'];
        }

        return $this->config['greencheck']['cachetime'];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function getCacheEnabled($key = 'default')
    {
        if ($this->isDisabled()) {
            return false;
        }

        if ('default' !== $key && isset($this->config['greencheck'][$key])) {
            return $this->config['greencheck'][$key]['cache'];
        }

        return $this->config['greencheck']['cache'];
    }

    /**
     * @return string
     */
    public function getCacheType()
    {
        if (isset($this->config['greencheck']['cachetype'])) {
            return $this->config['greencheck']['cachetype'];
        }
        return 'apc';
    }
    /**
     * @return string
     */
    public function getRedisHost ()
    {
        if (isset($this->config['greencheck']['redis']['host'])) {
            return $this->config['greencheck']['redis']['host'];
        }
        return '127.0.0.1';
    }

    /**
     * @return string
     */
    public function getMemcacheHost ()
    {
        if (isset($this->config['greencheck']['memcache']['host'])) {
            return $this->config['greencheck']['memcache']['host'];
        }
        return '127.0.0.1';
    }

    /**
     * @todo Inject these dependencies in the constructor instead
     *
     * @param string $cachetype
     * @return ApcCache|MemcacheCache|RedisCache
     */
    public function getCacheDriver(string $cachetype)
    {
        if ('memcache' == $cachetype) {
            $memcache = new \Memcache();
            $memcache->connect($this->getMemcacheHost(), 11211);
            $cache = new MemcacheCache();
            $cache->setMemcache($memcache);
        } elseif ('redis' == $cachetype) {
            $redis = new \Redis();
            $redis->connect($this->getRedisHost(), 6379);

            $cache = new RedisCache();
            $cache->setRedis($redis);
        } else {
            $cache = new ApcCache();
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

    /**
     *
     */
    public function disableCache()
    {
        $this->disabled = true;
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function resetCache(string $key)
    {
        if (isset($this->cache[$key])) {
            $cache = $this->cache[$key];
            // clear all
            $cache->deleteAll();
        }
    }

    /**
     * @param string $key
     * @param null $cache
     */
    public function setCache($key, $cache = null)
    {
        if (!is_null($cache)) {
            $this->cache[$key] = $cache;
        } else {
            $this->cache[$key] = $this->setupCache($key);
        }
    }

    /**
     * @param string $key
     * @return mixed|DisabledCache
     */
    public function getCache($key = 'default')
    {
        if (!isset($this->cache[$key])) {
            $this->setCache($key);
        }

        if ($this->disabled) {
            return new DisabledCache();
        }

        return $this->cache[$key];
    }

    /**
     * @param string $cache
     * @param string $key
     * @param mixed $data
     */
    public function setItem($cache, $key, $data)
    {
        $ttl = $this->getTTL($cache);
        $this->getCache($cache)->save(sha1($key), $data, $ttl);
    }
}
