<?php
namespace Library\Core;

/**
 * Bundles managment
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Bundles
{

    /**
     * Available bundles, controllers and actions
     * @var array
     */
    protected $aAvailableBundles = array();

    /**
     * \Library\Core\Bundles::$aAvailableBundles \Library\Core\Cache duration in seconds
     * @var integer
     */
    protected static $iBundlesCacheDuration = 1314000;

    public function __construct()
    {
        // Load available bundles
        self::build();
    }

    /**
     * Deploy bundle's javascript, css and images assets
     *
     * @todo bientôt les assets js et css seront compilé dans les librairies directement donc uniquement pour les images
     *
     * @throws AppException
     * @return array                    Deployed bundles
     */
    public static function deploy()
    {
        $aDeployedBundles = array();
        // Clean bundle's assets
        foreach ($this->aAvailableBundles as $sBundleName=>$aControllers) {
            if (is_dir(PUBLIC_BUNDLES_PATH . $sBundleName)) {
                if (! Directory::delete(PUBLIC_BUNDLES_PATH . $sBundleName)) {
                    throw  new AppException('Unable to flush bundle\'s assets, check chmod on ' . PUBLIC_BUNDLES_PATH . ' for user ' . self::getServerUsername());
                }
            }

            if (! Directory::create(PUBLIC_BUNDLES_PATH . $sBundleName, 0777, true)) {
                throw  new AppException('Unable to create bundle\'s assets directory, check chmod on ' . PUBLIC_BUNDLES_PATH . ' for user ' . self::getServerUsername());
            } else {
                $aDeployedBundles[] = $sBundleName;
            }

            // @todo soon deleted because of Assets::buildAssets() method
            $sDeployBundlesAssetsCommandLine = 'ln -s ' . BUNDLES_PATH . $sBundleName . '/Assets/* ' . PUBLIC_BUNDLES_PATH . $sBundleName . '/';
            echo exec($sDeployBundlesAssetsCommandLine);
        }
        return $aDeployedBundles;
    }

    /**
     * Get an array of all app bundles
     * @return array                        A three dimensional array that contain each module along with his own controllers and methods (actions only)
     */
    public function build()
    {
        assert('is_dir(BUNDLES_PATH)');
        $this->aAvailableBundles = array();

        $aBundles = \Library\Core\Cache::get(\Library\Core\Cache::getKey(get_called_class(), 'aAppBundlesTree'));
        if ($aBundles === false || count($aBundles) === 0) {
            $aBundles = array_diff(scandir(BUNDLES_PATH), array(
                '..',
                '.',
                'composer',
                'autoload.php'
            ));
            foreach ($aBundles as $sBundle) {
                $this->aAvailableBundles[$sBundle] = Controller::build($sBundle);
            }
            $aBundles = $this->aAvailableBundles;
            Cache::set(\Library\Core\Cache::getKey(get_called_class(), 'aAppBundlesTree'), $this->aAvailableBundles, false, self::$iBundlesCacheDuration);
        }
        return $this->aAvailableBundles;
    }

    /**
     * Return all availables bundles, controllers and actions
     * @return array
     */
    public function get()
    {
        return $this->build();
    }
}
