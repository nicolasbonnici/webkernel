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
     * The directory that contain built assets (must be relative to PUBLIC_PATH constant that don't start with "/")
     * @var string
     */
    protected $sBuildPath = 'lib/build/';

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
        $this->sBuildPath =         PUBLIC_PATH . $this->sBuildPath;
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
            $oAssetsPackages = new Json(Files::getContent(CONF_PATH . 'assets.ini'));
            /**
             * Load new Assets instance to register libs
            */
            $oAssets = new Assets();
            foreach ($oAssetsPackages->get('dependancies') as $sPackageType=>$aPackages) {
                foreach ($aPackages as $aPackage) {
                    $oAssets->register($aPackage, $sPackageType);
                }
            }
            foreach ($oAssetsPackages->get('core') as $sPackageType=>$aPackages) {
                foreach ($aPackages as $aPackage) {
                    $oAssets->register($aPackage, $sPackageType);
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
                    $sMinifiedJsCode .= \Library\Core\Minify::js(Files::getContent(PUBLIC_PATH . $sJsAsset));
                }
            } elseif ($sAssetType === 'css') {
                foreach ($aLibFilesPaths as $sCssAsset) {
                    $sMinifiedCssCode .= \Library\Core\Minify::css(Files::getContent(PUBLIC_PATH . $sCssAsset));
                }
            }
        }
        Tools::chmod($this->sBuildPath, array(0,7,7,7));
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
