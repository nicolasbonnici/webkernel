<?php
namespace Library\Core\Cache;

/**
 * Cache abstract layer
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
interface CacheInterface
{

    /**
     * Predifened constants for easier use/reading of cache time durations
     *
     * @var integer
     */
    const CACHE_TIME_MINUTE = 60;
    const CACHE_TIME_HALF_HOUR = 180;
    const CACHE_TIME_HOUR = 360;
    const CACHE_TIME_HALF_DAY = 43200;
    const CACHE_TIME_DAY = 86400;

    /**
     * Default cache time in seconds
     */
    const CACHE_TIME_DEFAULT= 120;

    /**
     * Default cache key prefix
     */
    const CACHE_KEY_PREFIX = 'Core';

    /**
     * Connect to the cache engine
     * @return void                 set CacheAbstract::$bIsConnected member to true
     */
    public static function connect();

    /**
     * Get cached value
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name);

    /**
     * Set new cache value
     *
     * @param string $name
     * @param mixed $value
     * @param bool $flag
     * @param int $expire
     * @return bool
     */
    public static function set($name, $value, $flag = false, $expire = self::CACHE_TIME_DEFAULT);

    /**
     * Delete from cache engine
     * @param string $name
     * @param int $timeout
     * @return bool
     */
    public static function delete($name, $timeout = 0);

    /**
     * Flush all data
     *
     * @param string $index
     * @return bool
     */
    public static function flush();

}
