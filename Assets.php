<?php
namespace Library\Core;

/**
 * Javascript and stylesheet assets managment
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Assets
{
    /**
     * The full file path that contain the javascript built code (must be relative to PUBLIC_PATH constant that don't start with "/")
     * @var string
     */
    protected $sJsBuildFilePath = 'lib/js/script.min.js';

    /**
     * The file that contain the CSS built code (must be relative to PUBLIC_PATH constant that don't start with "/")
     * @var string
     */
    protected $sCssBuildFilePath = 'lib/css/style.min.css';

    /**
     * Supported asset types
     * @var array
     */
    protected $aAssetTypes = array('js', 'css');

    /**
     * Registered assets to load and build
     * @var array               A two dimensional array that contain $sAssetType => $aAssets
     */
    protected $aAssets   = array();

    public function __construct()
    {
        // Build paths
        $this->sJsBuildFilePath =   PUBLIC_PATH . $this->sJsBuildFilePath;
        $this->sCssBuildFilePath =  PUBLIC_PATH . $this->sCssBuildFilePath;
    }

    /**
     * Register assets from anywhere!
     * @throws AppException
     */
    public function load()
    {
        if (! Files::exists(CONF_PATH . 'assets.json')) {
            throw new AppException('Unable to assets configuration...');
        } else {
            // Register javascript and stylesheet assets from configuration
            $oAssetsPackages = new Json(Files::getContent(CONF_PATH . 'assets.json'));

            /**
             * Load new Assets instance to register libs
             * @todo rendre tout ca plus generique et souple de facon a pouvoir le surcharger aisement de partout
            */
            $oAssets = new Assets();
            foreach ($oAssetsPackages->get('dependancies') as $sPackageType=>$aPackages) {
                foreach ($aPackages as $aPackage) {
                    $this->register($aPackage, $sPackageType);
                }
            }
            foreach ($oAssetsPackages->get('core') as $sPackageType=>$aPackages) {
                foreach ($aPackages as $aPackage) {
                    $this->register($aPackage, $sPackageType);
                }
            }
        }
    }

    /**
     * Minify and concatenate all Ux and bundles javascript and stylesheet assets
     *
     * @throws AppException
     * @return boolean                  TRUE if all went smooth otherwhise FALSE
     */
    public function build()
    {
        $sMinifiedJsCode = '';
        $sMinifiedCssCode = '';

        // Collect registered assets
        foreach ($this->aAssets as $sAssetType=>$aLibFilesPaths) {
            if ($sAssetType === 'js') {
                foreach ($aLibFilesPaths as $sJsAsset) {
                    $sMinifiedJsCode .= \Library\Core\Minify::js(Files::getContent(PUBLIC_PATH . $sJsAsset), substr(PUBLIC_PATH . $sJsAsset, 0));
                }
            } elseif ($sAssetType === 'css') {
                foreach ($aLibFilesPaths as $sCssAsset) {
                    // Correct the absolute path path if needed
                    if (mb_substr($sCssAsset, 0, 1) === DIRECTORY_SEPARATOR) {
                        $sCssAsset = mb_substr($sCssAsset, 1);
                    }
                    $sMinifiedCssCode .= \Library\Core\Minify::css(Files::getContent(PUBLIC_PATH . $sCssAsset), substr(PUBLIC_PATH . $sCssAsset, 0, strripos(PUBLIC_PATH . $sCssAsset, DIRECTORY_SEPARATOR)));
                }
            }
        }
        if (
            Files::write($this->sJsBuildFilePath, $sMinifiedJsCode) &&
            Files::write($this->sCssBuildFilePath, $sMinifiedCssCode)
        ) {
            return (Files::exists($this->sJsBuildFilePath) && Files::exists($this->sCssBuildFilePath));
        }

        return false;
    }

    /**
     * Register assets
     *
     * @param string $sFilePath             Absolute asset file path
     * @param string $sType                 Asset type (must be declared on $this->aAssetTypes)
     * @throws AppException
     * @return boolean                      TRUE if all went smooth otherwhise FALSE
     */
    public function register($sFilePath, $sType)
    {
        if (empty($sFilePath) && ! Files::exists($sFilePath)) {
            throw  new AppException('Asset doesn\'t exists or no parameter provided!');
        } elseif(!in_array($sType, $this->aAssetTypes)) {
            throw  new AppException('Asset type (' . $sType . ') not supported!');
        } else {
            $this->aAssets[$sType][] = $sFilePath;
            return true;
        }
    }

    /**
     * Assets accessor
     * @return array
     */
    public function get()
    {
        return $this->aAssets;
    }
}
