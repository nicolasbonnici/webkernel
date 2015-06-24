<?php
namespace Library\Core\App;
use Library\Core\App\Mvc\Controller;
use Library\Core\Cache;

/**
 * Bundles management
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Bundles
{

    /**
     * \Library\Core\Bundles::$aBundles \Library\Core\Cache duration in seconds
     * @var integer
     */
    protected static $iBundlesCacheDuration = 1314000;

    protected $aExcludedItems = array(
        '..',
        '.',
        'composer',
        'autoload.php'
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
        // Load available bundles
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
        assert('is_dir(BUNDLES_PATH)');
        $this->aBundles = array();
        $this->aBundles = \Library\Core\Cache::get(\Library\Core\Cache::getKey(get_called_class(), 'aBundlesDistribution'));
        if ($bFlushBundlesCache || $this->aBundles === false ) {
            $this->parseBundles();
        }
    }

    /**
     * Parse all available bundles the store them on the cache engine
     */
    private function parseBundles()
    {
        $aBundles = array_diff(scandir(BUNDLES_PATH), $this->aExcludedItems);
        foreach ($aBundles as $iIndex=>$sBundle) {
            $this->aBundles[$sBundle] = null;
        }
        Cache::set(\Library\Core\Cache::getKey(get_called_class(), 'aBundlesDistribution'), $this->aBundles, false, self::$iBundlesCacheDuration);
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
