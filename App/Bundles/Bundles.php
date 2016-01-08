<?php
namespace Library\Core\App\Bundles;

use Library\Core\Bootstrap;
use Library\Core\Cache\Drivers\Memcache;

/**
 * Bundles management
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Bundles
{

    const BUNDLES_CACHE_KEY = 'aProjectBundles';

    /**
     * \Library\Core\Bundles::$aBundles Cache duration in seconds
     * @var integer
     */
    protected static $iBundlesCacheDuration = 1314000;

    protected $aExcludedItems = array(
        '..',
        '.',
        'composer',
        'autoload.php',
        'EMPTY'
    );

    /**
     * Available bundles and MVC structure
     * @var array
     */
    protected $aBundles = array();

    /**
     * Instance constructor
     *
     * @param bool $bFlushBundlesCache TRUE to flush bundle's cache
     */
    public function __construct($bFlushBundlesCache = false)
    {
        $this->build($bFlushBundlesCache);
    }

    /**
     * Get an array of all app bundles
     *
     * @param bool $bFlushBundlesCache
     * @return array                        A three dimensional array that contain each module along with his own controllers and methods (actions only)
     */
    protected function build($bFlushBundlesCache)
    {
        $this->aBundles = array();
        $this->aBundles = Memcache::get(Memcache::getKey(get_called_class(), self::BUNDLES_CACHE_KEY));
        if ($bFlushBundlesCache || $this->aBundles === false) {
            $this->parseBundles();
        }
    }

    /**
     * Parse all available bundles the store them on the cache engine
     */
    private function parseBundles()
    {
        $aBundles = array_diff(scandir(Bootstrap::getPath(Bootstrap::PATH_BUNDLES)), $this->aExcludedItems);
        foreach ($aBundles as $iIndex=>$sBundle) {
            $this->aBundles[$sBundle] = null;
        }
        Memcache::set(Memcache::getKey(get_called_class(), self::BUNDLES_CACHE_KEY), $this->aBundles, self::$iBundlesCacheDuration);
    }

    /**
     * Return all availables bundles, controllers and actions
     * @return array
     */
    public function get()
    {
        return $this->aBundles;
    }
}
