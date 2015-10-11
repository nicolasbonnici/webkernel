<?php
namespace Library\Core\App\Mvc\View\Assets;

use Library\Core\Exception\CoreException;

use Library\Core\FileSystem\File;
use Library\Core\Json\Json;

/**
 * Javascript and css assets management
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */

class Assets
{
    /**
     * Supported asset type
     * @var string
     */
    const TYPE_JAVASCRIPT = 'js';
    const TYPE_STYLESHEET = 'css';

    /**
     * The full path that contain the generated javascript code
     * Must be relative to PUBLIC_PATH (project/public/) constant and don't start with a "/"
     * @see instance instance constructor
     * @var string
     */
    protected $sBuildPath = 'min/';

    /**
     * Supported asset types
     * @var array
     */
    protected $aAllowedAssetTypes = array(
        self::TYPE_JAVASCRIPT,
        self::TYPE_STYLESHEET
    );

    /**
     * Registered assets to load and build
     * @var array               A two dimensional array that contain $sAssetType => $aAssets
     */
    protected $aAssets   = array();

    public function __construct()
    {
        // Build paths
        $this->sBuildPath =   PUBLIC_PATH . $this->sBuildPath;

        // Load client component package's assets
        $this->load();
    }

    /**
     * Register assets from anywhere!
     * @throws AssetsException
     */
    public function load()
    {
        if (! File::exists(CONF_PATH . 'assets.json')) {
            throw new AssetsException('Unable to assets configuration...');
        } else {
            // Register javascript and stylesheet assets from configuration
            $oAssetsPackages = new Json(File::getContent(CONF_PATH . 'assets.json'));

            foreach ($oAssetsPackages->get() as $sPackageName=>$oPackages) {
                foreach ($oPackages as $sPackageType=>$aPackage) {
                    foreach ($aPackage as $sAssetPath) {
                        // Register component package's assets
                        $this->register($sAssetPath, $sPackageType, $sPackageName);
                    }
                }
            }

        }
    }

    /**
     * Minify and concatenate all client components
     *
     * @throws AssetsException
     * @return boolean|array                  TRUE if all went smooth otherwise the log as array
     */
    public function build()
    {
        $aBuiltLog = array();
        // Collect registered components
        foreach ($this->aAssets as $sPackageName=>$aLibFilesPaths) {
            $sMinifiedJsCode = '';
            $sMinifiedCssCode = '';

            // Minify component assets
            foreach ($aLibFilesPaths as $sAssetType=>$aLibFilesPaths) {

                foreach ($aLibFilesPaths as $sAsset) {

                    # Check and clean for DIRECTORY_SEPARATOR at start
                    if (substr($sAsset, 0, 1) === DIRECTORY_SEPARATOR) {
                        $sAsset = substr($sAsset, 1);
                    }

                    $sCode = File::getContent(PUBLIC_PATH . $sAsset);
                    if ($sCode !== false && empty($sCode) === false) {
                        switch($sAssetType) {
                            case self::TYPE_JAVASCRIPT :
                                $sMinifiedJsCode .= Minify::js($sCode);
                                break;
                            case self::TYPE_STYLESHEET :
                                $sMinifiedCssCode .= Minify::css($sCode);
                                break;
                        }

                    }
                }

            }

            if (empty($sMinifiedJsCode) === false) {
                $aBuiltLog[$sPackageName . '_js'] = $this->writeMinifiedComponent(
                    $sMinifiedJsCode,
                    $sPackageName,
                    self::TYPE_JAVASCRIPT
                );
            }

            if (empty($sMinifiedCssCode) === false) {
                $aBuiltLog[$sPackageName . '_css'] = $this->writeMinifiedComponent(
                    $sMinifiedCssCode,
                    $sPackageName,
                    self::TYPE_STYLESHEET
                );
            }


        }

        return (in_array(false, $aBuiltLog) === false) ? true : $aBuiltLog;
    }

    /**
     * Persist minified code on files
     *
     * @param string $sMinifiedCssCode
     * @param string $sPackageName
     * @param string $sAssetType
     */
    private function writeMinifiedComponent($sMinifiedCssCode, $sPackageName, $sAssetType)
    {
        $sMinifiedFileName = $this->computeMinifiedFilePath($sPackageName, $sAssetType);
        if (empty($sMinifiedCssCode) === false) {
            return File::write(
                $sMinifiedFileName,
                $sMinifiedCssCode
            );
        }

        # Clean minified component not needed anymore
        if (File::exists($sMinifiedFileName) === true) {
            File::delete($sMinifiedFileName);
        }

        return false;
    }

    /**
     * @param string $sPackageName
     * @param string $sAssetType
     * @return string
     */
    private function computeMinifiedFilePath($sPackageName, $sAssetType)
    {
        return $this->sBuildPath . $sPackageName . '.min.' . $sAssetType;
    }

    /**
     * Build all client compoenents packages assets
     *
     * @ see app/config/layout.json
     * @param array $aComponents one dimensional array of string that represent component name
     * @return array
     */
    public function buildClientComponents($aComponents)
    {
        $aClientComponentAssets = array(
        	self::TYPE_STYLESHEET => array(),
        	self::TYPE_JAVASCRIPT => array()
        );
        foreach ($aComponents as $iIndex=>$sComponentName) {
            // If the component declaration is found under assets configuration
            if (array_key_exists($sComponentName, $this->aAssets)) {
                foreach ($this->aAssets[$sComponentName][self::TYPE_STYLESHEET] as $iCssAssetIndex=>$sCssAssetPath) {
                    $aClientComponentAssets[self::TYPE_STYLESHEET][] =  $sCssAssetPath;
                }
                foreach ($this->aAssets[$sComponentName][self::TYPE_JAVASCRIPT] as $iJsAssetIndex=>$sJsAssetPath) {
                    $aClientComponentAssets[self::TYPE_JAVASCRIPT][] = $sJsAssetPath;
                }
            }
        }
        return $aClientComponentAssets;
    }

    /**
     * Register assets
     *
     * @param string $sFilePath             Absolute asset file path
     * @param string $sType                 Asset type (must be declared on $this->aAllowedAssetTypes)
     * @param string $sPackageName          Assets package name
     * @throws AssetsException
     * @return boolean                      TRUE if all went smooth otherwhise FALSE
     */
    public function register($sFilePath, $sType, $sPackageName = 'core')
    {
        if (empty($sFilePath) && ! File::exists($sFilePath)) {
            throw  new AssetsException('Asset doesn\'t exists or no parameter provided.');
        } elseif(!in_array($sType, $this->aAllowedAssetTypes)) {
            throw  new AssetsException('Asset type (' . $sType . ') not supported.');
        } else {
            $this->aAssets[$sPackageName][$sType][] = $sFilePath;
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

    /**
     * Resolve available assets by type for a given client component
     *
     * @param string $sPackageName
     * @param string $sAssetType
     * @return null|string
     */
    public function getMinifiedPublicPath($sPackageName, $sAssetType)
    {
        $sMinifiedPath = $this->computeMinifiedFilePath($sPackageName, $sAssetType);
        return (File::exists($sMinifiedPath))
            ? str_replace(PUBLIC_PATH, '/', $sMinifiedPath)
            : null;
    }

    /**
     * @return array
     */
    public function getAllowedAssetTypes()
    {
        return $this->aAllowedAssetTypes;
    }
}

class AssetsException extends CoreException {

}