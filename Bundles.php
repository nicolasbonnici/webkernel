<?php
namespace Library\Core;

/**
 * Bundles managment
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Bundles
{

    protected $aAvailableBundles = array();

    public function __construct()
    {
        // Load available bundles
        $this->aAvailableBundles = App::getBundles();
    }

    /**
     * Deploy bundle's javascript, css and images assets
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

}
