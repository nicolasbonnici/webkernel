<?php
namespace Library\Core;

/**
 * Memcached
 *
 * @ŧodo decoupler en un componsant Cache abstract étendue par des drivers pour utiliser d'autres moteur de cache plus tard
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 */
class Cache
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

    const prefix = 'Core';

    private static $res_memcache;

    private static $connected;

    private static function memc_res()
    {
        $memcacheServer = CACHE_HOST;
        $memcachePort = CACHE_PORT;
        if (! isset(self::$res_memcache)) {
            if ($memcacheServer && $memcachePort) {
                self::$res_memcache = new \Memcache();
                if (self::$res_memcache->connect($memcacheServer, $memcachePort, 2) === true) {
                    self::$connected = true;
                    return true;
                } else {
                    self::$connected = false;
                    return false;
                }
            } else {
                throw new \Exception("Memcache n'est pas configuré dans le fichier config.ini.<br>" . "server : '" . $memcacheServer . "'," . "port : '" . $memcachePort . "'");
            }
        }
    }

    public static function get($name)
    {
        if (isset($_GET['noCache']) && ENV === 'dev') {
            return false;
        }
        
        if (self::$connected) {
            $ret = self::$res_memcache->get(self::prefix . '-' . $name);
        } elseif (self::memc_res()) {
            $ret = self::$res_memcache->get(self::prefix . '-' . $name);
        } else {
            $ret = false;
        }
        
        if (isset($_GET['clearCache']) && ENV === 'dev' && self::$connected) {
            self::$res_memcache->delete(self::prefix . '-' . $name);
            $ret = false;
        }
        
        return $ret;
    }

    public static function set($name, $value, $flag = false, $expire = 120)
    {
        if (self::$connected) {
            self::$res_memcache->set(self::prefix . '-' . $name, $value, false, $expire);
        } elseif (self::memc_res()) {
            self::$res_memcache->set(self::prefix . '-' . $name, $value, false, $expire);
        } else {
            return false;
        }
    }

    public static function delete($name, $timeout = 0)
    {
        if (self::$connected) {
            self::$res_memcache->delete(self::prefix . '-' . $name, $timeout);
        } elseif (self::memc_res()) {
            self::$res_memcache->delete(self::prefix . '-' . $name, $timeout);
        } else {
            return false;
        }
    }

    public static function flush($index = '')
    {
        if (self::$connected) {
            self::$res_memcache->flush();
        } elseif (self::memc_res()) {
            self::$res_memcache->flush();
        } else {
            return false;
        }
    }

    /**
     * Generate cache key depending on given parameters
     * Variable types "ressource" and "NULL" and "Unknow type" are not handled
     * If key string is more than 250 characters long, MD5 hash is retrieved (Memcache limitation)
     *
     * @since 1.1.0
     * @return string Cache key
     */
    public static function getKey()
    {
        $sKey = '';
        foreach (func_get_args() as $mArgument) {
            switch (gettype($mArgument)) {
                case 'integer':
                case 'double':
                case 'string':
                    $sKey .= $mArgument . '-';
                    break;
                case 'boolean':
                    $sKey .= (string) $mArgument . '-';
                    break;
                case 'array':
                    $sKey .= call_user_func_array(array(
                        'self',
                        'getKey'
                    ), $mArgument) . '-';
                    break;
                case 'object':
                    $sKey .= serialize($mArgument) . '-';
                    break;
            }
        }
        
        if (strlen($sKey) > 250) {
            $sKey = md5($sKey);
        }
        
        return $sKey;
    }
}
