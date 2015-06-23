<?php
namespace Library\Core\Cache\Drivers;
use Library\Core\Cache\CacheAbstract;
use Library\Core\Cache\CacheInterface;

/**
 * Memcache driver
 *
 * @author niko <nicolasbonnici@gmail.com>
 */
class Memcache extends CacheAbstract
{

    /**
     * Memcache ressource
     * @var \memcache
     */
    private static $res_memcache;

    /**
     * Enable zlib data compression
     * @var int
     */
    protected static $iZLibSupport = 0;

    protected function connect()
    {
        $memcacheServer = CACHE_HOST;
        $memcachePort = CACHE_PORT;
        if (! isset(self::$res_memcache)) {
            if ($memcacheServer && $memcachePort) {
                self::$res_memcache = new \Memcache();
                if (self::$res_memcache->connect($memcacheServer, $memcachePort, 2) === true) {
                    self::$bIsConnected = true;
                    return true;
                } else {
                    self::$bIsConnected = false;
                    return false;
                }
            } else {
                throw new \Exception("Memcache n'est pas configuré dans le fichier config.ini.<br>" . "server : '" . $memcacheServer . "'," . "port : '" . $memcachePort . "'");
            }
        }
    }

    public static function get($name)
    {
        // @todo quick and dirty
        if (isset($_GET['noCache']) && ENV === 'dev') {
            return false;
        }
        
        if (self::isConnected()) {
            $ret = self::$res_memcache->get(self::CACHE_KEY_PREFIX . '-' . $name);
        } elseif (self::connect()) {
            $ret = self::$res_memcache->get(self::CACHE_KEY_PREFIX . '-' . $name);
        } else {
            $ret = false;
        }
        
        if (isset($_GET['clearCache']) && ENV === 'dev' && self::$bIsConnected) {
            self::$res_memcache->delete(self::CACHE_KEY_PREFIX . '-' . $name);
            $ret = false;
        }
        
        return $ret;
    }

    /**
     *
     * @todo modifier la signature pour apporter la gestion du parametre $bZLib par le membre du driver idem interface
     *
     * @param string $name
     * @param mixed $value
     * @param int $bZLibPacked
     * @param int $expire
     * @return bool
     * @throws \Exception
     */
    public static function set($name, $value, $bZLibPacked = 0, $expire = CacheInterface::CACHE_TIME_DEFAULT)
    {
        if (self::isConnected()) {
            self::$res_memcache->set(self::CACHE_KEY_PREFIX . '-' . $name, $value, $bZLibPacked, $expire);
        } elseif (self::connect()) {
            self::$res_memcache->set(self::CACHE_KEY_PREFIX . '-' . $name, $value, $bZLibPacked, $expire);
        } else {
            return false;
        }
    }

    public static function delete($name, $timeout = 0)
    {
        if (self::isConnected()) {
            self::$res_memcache->delete(self::CACHE_KEY_PREFIX . '-' . $name, $timeout);
        } elseif (self::connect()) {
            self::$res_memcache->delete(self::CACHE_KEY_PREFIX . '-' . $name, $timeout);
        } else {
            return false;
        }
    }

    public static function flush()
    {
        if (self::isConnected()) {
            self::$res_memcache->flush();
        } elseif (self::connect()) {
            self::$res_memcache->flush();
        } else {
            return false;
        }
    }

    /**
     * Enable data compression (using zlib)
     * @return void
     */
    public static function enableZLibSupport()
    {
        self::$iZLibSupport = \MEMCACHE_COMPRESSED;
    }

    /**
     * Disable data compression
     *
     * @return void
     */
    public static function disableZLibSupport()
    {
        self::$iZLibSupport = 0;
    }
}
