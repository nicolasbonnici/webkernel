<?php

namespace Library\Core;

/**
 * App Model class
 * A simple class to build and manage usefull setup informations
 *
 * @dependancy \Library\Core\Cache
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */

class App extends Singleton
{

    /**
     * Current framework version
     * @var string
     */
    const APP_VERSION = '1.0';

    /**
     * Current framework release name
     * @var string
     */
    const APP_RELEASE_NAME = 'ihop';

    /**
     * Available bundles, controllers and actions
     *
     * @var array
     */
    private static $aBundles;

    /**
     * \Library\Core\Bootstrap::$aBundles \Library\Core\Cache duration in seconds
     *
     * @var integer
     */
    protected static $iBundlesCacheDuration = 1314000;


    protected static $sPhpVersion;

    public function __construct()
    {
        /**
         * Parse and load bundles, controllers and actions available
         */
        self::buildBundles();

        self::$sPhpVersion = PHP_VERSION;
        // @todo SGBD infos

    }

    /**
     * Init template engine and render view
     *
     * @param string $sTpl
     * @param array $aViewParams
     * @param boolean $bToString
     * @param boolean $bLoadAllBundleViews         A flag to load all bundles views path (For the CrudController)
     */
    public static function initView($sTpl, $aViewParams, $bToString, $bLoadAllBundleViews = false)
    {
        $sHaangaPath = LIBRARY_PATH . 'Haanga/';
        require_once $sHaangaPath . 'Haanga.php';

        $aViewsPaths = array (
                APP_PATH . 'Views/',
                BUNDLES_PATH . \Library\Core\Router::getBundle() . '/Views/'
        );

        if ($bLoadAllBundleViews && count(self::$aBundles) > 0) {
            foreach (self::$aBundles as $sBundle=>$aController) {
                if ($sBundle !== \Library\Core\Router::getBundle()) {
                    $aViewsPaths[] = BUNDLES_PATH . $sBundle . '/Views/';
                }
            }
        }

        \Haanga::configure ( array (
                'template_dir' => $aViewsPaths,
                'cache_dir' => CACHE_PATH . \Library\Core\Router::getBundle() . '/Views'
        ) );

        return \Haanga::load ( $sTpl, $aViewParams, $bToString );
    }

    /**
     * Get an array of all app bundles
     *
     * @return array                        A three dimensional array that contain each module along with his own controllers and methods (actions only)
     */
    public static function buildBundles()
    {
        assert('is_dir(BUNDLES_PATH)');
        $aParsedBundles = array();

        self::$aBundles = \Library\Core\Cache::get(\Library\Core\Cache::getKey(get_called_class(), 'aAppBundlesTree'));
        if (self::$aBundles === false || count(self::$aBundles) === 0) {
            $aBundles = array_diff(scandir(BUNDLES_PATH), array('..', '.', 'composer', 'autoload.php'));
            foreach($aBundles as $sBundle) {
                $aParsedBundles[$sBundle] = self::buildControllers($sBundle);
            }
            self::$aBundles = $aParsedBundles;
            Cache::set(\Library\Core\Cache::getKey(get_called_class(), 'aAppBundlesTree'), $aParsedBundles, false, self::$iBundlesCacheDuration);
        }
    }

    /*
     * Get an array of all Controllers  and methods for a given module
     *
     * @param string $sBundle               The module name
     * @return array                        A two dimensional array that contain each controller from a module along with his own methods (actions only)
     */
    public static function buildControllers($sBundle)
    {

        assert('!empty($sBundle) && is_string($sBundle) && is_dir(BUNDLES_PATH . "/" . $sBundle . "/Controllers/")');

        $aControllers = array();
        $sControllerPath = BUNDLES_PATH . '/' . $sBundle . '/Controllers/';
        $aFiles = array_diff(scandir($sControllerPath), array('..', '.'));

        foreach ($aFiles as $sController) {
            if (preg_match('#Controller.php$#', $sController)) {
                $aControllers[substr($sController, 0, strlen($sController) - strlen('Controller.php'))] = self::buildActions($sBundle, $sController);
            }
        }

        return $aControllers;
    }

    /**
     * Get all actions from a given module and controller (this method only return [foo]Action() methods)
     *
     * @param string $sBundle               The module name
     * @param string $sController           The controller name to parse
     * @return array                        A two dimensional array with the controllers and their methods (actions only)
     */
    public static function buildActions($sBundle, $sController)
    {

        assert('!empty($sController) && is_string($sController) && !empty($sBundle) && is_string($sBundle)');
        $aActions = array();
        $aMethods = get_class_methods('\bundles\\' . $sBundle . '\Controllers\\' . substr($sController, 0, strlen($sController) - strlen('.php')));
        if (count($aMethods) > 0) {
            foreach ($aMethods as $sMethod) {
                if (preg_match('#Action$#', $sMethod) && $sMethod !== 'getAction' && $sMethod !== 'setAction') {
                    $aActions[] = substr($sMethod, 0, strlen($sMethod) - strlen('Action'));
                }
            }
        }

        return $aActions;
    }

    public static function getPhpVersion()
    {
        return self::$sPhpVersion;
    }
}