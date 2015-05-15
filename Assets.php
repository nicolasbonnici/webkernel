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
     * The full path that contain the generated javascript code
     * Must be relative to PUBLIC_PATH (project/public/) constant and don't start with a "/"
     * @see instance instance constructor
     * @var string
     */
    protected $sBuildePath = 'min/';

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
        $this->sBuildPath =   PUBLIC_PATH . $this->sBuildePath;

        // Load client component package's assets
        $this->load();
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
     * @throws AppException
     * @return boolean|array                  TRUE if all went smooth otherwhise the log as an array
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
                if ($sAssetType === 'js') {
                    foreach ($aLibFilesPaths as $sJsAsset) {
                    	if (mb_substr($sJsAsset, 0, 1) === DIRECTORY_SEPARATOR) {
                    		$sJsAsset = mb_substr($sJsAsset, 1);
                    	}                    	
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

            if (! empty($sMinifiedJsCode)) {
                $aBuiltLog[$sPackageName . '_js']  = Files::write($this->sBuildPath . $sPackageName . '.min.js', $sMinifiedJsCode);
            }

            if (! empty($sMinifiedCssCode)) {
                $aBuiltLog[$sPackageName . '_css'] = Files::write($this->sBuildPath . $sPackageName . '.min.css', $sMinifiedCssCode);
            }

        }

        return (in_array(false, $aBuiltLog) === false) ? true : $aBuiltLog;
    }

    /**
     * Build all client compoenents packages assets
     *
     * @ see app/config/layout.json
     * @param array $aComponents one dimensional array of string that represent component name
     * @return Ambigous <multitype:multitype: , unknown>
     */
    public function buildClientComponents($aComponents)
    {
        $aClientComponentAssets = array(
        	'css' => array(),
        	'js' => array()
        );
        foreach ($aComponents as $iIndex=>$sComponentName) {
            // If the component declaration is found under assets configuration
            if (array_key_exists($sComponentName, $this->aAssets)) {
                foreach ($this->aAssets[$sComponentName]['css'] as $iCssAssetIndex=>$sCssAssetPath) {
                    $aClientComponentAssets['css'][] =  $sCssAssetPath;
                }
                foreach ($this->aAssets[$sComponentName]['js'] as $iJsAssetIndex=>$sJsAssetPath) {
                    $aClientComponentAssets['js'][] = $sJsAssetPath;
                }
            }
        }
        return $aClientComponentAssets;
    }

    /**
     * Register assets
     *
     * @param string $sFilePath             Absolute asset file path
     * @param string $sType                 Asset type (must be declared on $this->aAssetTypes)
     * @param string $sPackageName          Assets package name
     * @throws AppException
     * @return boolean                      TRUE if all went smooth otherwhise FALSE
     */
    public function register($sFilePath, $sType, $sPackageName = 'core')
    {
        if (empty($sFilePath) && ! Files::exists($sFilePath)) {
            throw  new AppException('Asset doesn\'t exists or no parameter provided.');
        } elseif(!in_array($sType, $this->aAssetTypes)) {
            throw  new AppException('Asset type (' . $sType . ') not supported.');
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
}
