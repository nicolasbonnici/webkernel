<?php
namespace Library\Core\App\Mvc\View;

use Library\Core\App\Bundles\Bundles;
use Library\Core\App\Mvc\Controller;
use Library\Core\App\Mvc\View\Assets\Assets;
use Library\Core\Bootstrap;
use Library\Core\FileSystem\Directory;
use Library\Core\Router;
use Library\Core\Json\Json;

/**
 * View rendering engine
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 * @see \Library\Haanga
 */
class View
{
    /**
     * Blank layout template file path
     * @see instance constructor
     * @var string
     */
    const BLANK_LAYOUT = 'layout_blank.tpl';

    /**
     *  Assets managment
     * @var \Library\Core\App\Mvc\View\Assets\Assets
     */
    private static $oAssetsInstance;

    /**
     * Registered client side components to load for the frontend view
     * @see app/config/assets.json
     * @var array
     */
    protected $aClientComponents = array(
        'dependencies',
        'ux'
    );

    /**
     * View instance constructor
     *
     * @param bool $bLoadAllBundleViews     Flag to load all bundles views path
     * @param array $aCustomPaths           Custom view templates paths
     */
    public function __construct($bLoadAllBundleViews = false, array $aCustomPaths = array())
    {
        $sHaangaPath = LIBRARY_PATH . 'Haanga/';
        require_once $sHaangaPath . 'Haanga.php';

        $aViewsPaths = array(
            BUNDLES_PATH . Router::getBundle() . '/Views/',
            APP_PATH . 'Views/'
        );

        if ($bLoadAllBundleViews) {
            $oBundles = new Bundles();
            foreach ($oBundles->get() as $sBundle => $aController) {
                if ($sBundle !== Router::getBundle()) {
                    $aViewsPaths[] = BUNDLES_PATH . $sBundle . '/Views/';
                }
            }
        }

        if (empty($aCustomPaths) === false) {
            foreach($aCustomPaths as $sRelativePath) {
                $aViewsPaths[] = $sRelativePath;
            }
        }

        // Setup client componetns dependancy managment
        self::$oAssetsInstance = new Assets();

        // Setup Haanga render engine
        \Haanga::configure(array(
            'template_dir' => $aViewsPaths,
            'cache_dir' => CACHE_PATH . Router::getBundle() . '/Views'
        ));
    }

    /**
     * Render request
     *
     * @param string $sTpl
     * @param integer $iStatusXHR
     * @param boolean $bToString
     */
    public function render(array $aViewParams, $sTpl = self::BLANK_LAYOUT, $iStatusXHR = Controller::XHR_STATUS_OK, $bToString = false)
    {

        // Debug
        $aViewParams["aLoadedClass"] = Bootstrap::getAutoloaderInstance()->getLoadedClass();

        // Benchmark
        $aViewParams['processing_time'] = round(microtime(true) - FRAMEWORK_STARTED, 3);

        // Client components
        $aViewParams['aClientComponents'] = $this->buildViewComponents();

        // check if it's an XMLHTTPREQUEST
        if (isset($aViewParams['bIsXhr']) && $aViewParams['bIsXhr'] === true) {
            $aCharsToStrip = array("\r", "\r\n", "\n", "\t");
            $oResponse = new Json(
                array(
                    'status' => $iStatusXHR,
                    'content' => str_replace($aCharsToStrip, '', $this->load($sTpl, $aViewParams, true)),
                    'debug' => isset($aViewParams["sDeBugHelper"]) ?
                        str_replace($aCharsToStrip, '', $this->load($aViewParams["sDeBugHelper"], $aViewParams, true)) :
                        null
                )
            );
            if ($bToString === true) {
                return $oResponse->__toString();
            }

            header('Content-Type: application/json');
            echo $oResponse;
            exit();
        }

        // Render the view using Haanga
        $this->load($sTpl, $aViewParams, $bToString);
    }

    /**
     * Init template engine and render view
     *
     * @param string $sTpl
     * @param array $aViewParams
     * @param boolean $bToString
     */
    private function load($sTpl, $aViewParams, $bToString)
    {
        return \Haanga::load($sTpl, $aViewParams, $bToString);
    }

    /**
     * Clear rendering engine cache files
     *
     * @param string $bRetry
     * @throws ViewException
     * @return boolean
     */
    public function clearCache($bRetry = false)
    {
        try {
            if (!Directory::deleteDirectory(CACHE_PATH)) {
                throw  new ViewException('Unable to clear cache folder (' . CACHE_PATH . ')');
            }
            return Directory::exists(CACHE_PATH);
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Register a new client component package
     *
     * @see app/config/assets.json
     * @param string $sComponentName
     * @return boolean
     */
    public function registerClientComponent($sComponentName)
    {
        if (
            is_string($sComponentName) === false ||
            empty($sComponentName) === true ||
            array_key_exists($sComponentName, $this->aClientComponents) === true
        ) {
            return false;
        } else {
            $this->aClientComponents[] = $sComponentName;
            return true;
        }
    }

    /**
     * Registered client components accessor
     *
     * @return array
     */
    public function buildViewComponents()
    {
        $oAsset = new Assets();
        $aComponents = array();

        $aAllowedTypes = $oAsset->getAllowedAssetTypes();
        foreach ($this->aClientComponents as $sPackage) {
            foreach ($aAllowedTypes as $sAllowedType) {
                $sComponentPath = $oAsset->getMinifiedPublicPath($sPackage, $sAllowedType);
                if (is_null($sComponentPath) === false) {
                    $aComponents[$sAllowedType][] = $sComponentPath;
                }
            }
        }
        return $aComponents;
    }

}

class ViewException extends \Exception
{
}