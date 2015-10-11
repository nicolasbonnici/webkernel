<?php
namespace Library\Core;

use Library\Core\FileSystem\File;
use Library\Core\Json\Json;
use Library\Core\Router;
use bundles\user\Entities\User;

/**
 * Bootstrap Model class
 * A simple class to build and manage usefull setup informations
 *
 * @dependancy \Library\Core\Cache
 *
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class Bootstrap
{
    private static $oInstance;

    /**
     * @var \Library\Core\Autoload
     */
    protected static $oAutoloaderInstance;

    /**
     * Router instance
     * @var \Library\Core\Router
     */
    private static $oRouterInstance;

    /**
     * Project global configuration
     * @var array
     */
    private static $aConfig;

    /**
     * Bundle config
     * Currently loaded bundle json configuration
     *
     * @var object
     */
    private static $oBundleConfig;

    /**
     * An array of dns
     *
     * @todo passer en config
     * @var array
     */
    private static $aEnvironements = array();

    /**
     * MVC request
     *
     * @var array
     */
    private static $aRequest;

    /**
     * PHP version
     * @var string
     */
    protected static $sPhpVersion;

    /**
     * Instance constructor
     */
    public function __construct()
    {
        // Grab microtime for benchmark purposes
        define('FRAMEWORK_STARTED', microtime(true));

        self::$sPhpVersion = PHP_VERSION;

        // @todo from conf
        self::initPaths();

        self::initAutoloader();

        self::initConfig();        

        self::initEnv();

        // Init Router component
        self::$oRouterInstance = Router::getInstance();
        self::$aRequest = self::initRouter();

        self::initReporting();

        self::initLogs();

        self::initCache();

        self::loadBundleConfig();

        self::initController();
    }

    public static function getInstance()
    {
        if (! self::$oInstance instanceof self) {
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }

    /**
     * Register class autoload
     */
    public static function initAutoloader()
    {
        require ROOT_PATH . '/Library/Core/Autoload.php';
        self::$oAutoloaderInstance = new Autoload();
        self::$oAutoloaderInstance->register();
    }

    /**
     * Init cache based on memcached
     */
    public static function initCache()
    {
        define('CACHE_HOST', self::$aConfig['cache']['host']);
        define('CACHE_PORT', self::$aConfig['cache']['port']);
    }

    /**
     * Init errors and notices reporting
     */
    public static function initReporting()
    {
        // init logs and errors reporting
        error_reporting((ENV === 'dev') ? -1 : 0);
        ini_set('display_errors', (ENV === 'dev') ? 'On' : 'Off');
        ini_set('log_errors', 'On');
    }

    /**
     * Init log file
     */
    public static function initLogs()
    {
        $sLogFile = LOG_PATH . '/errors.log';
        if (! is_file($sLogFile)) {

            if (! is_dir(LOG_PATH)) {
                mkdir(LOG_PATH);
            }

            // Reconstruire le chemin aussi
            if (! is_dir(substr($sLogFile, 0, strlen($sLogFile) - strlen('/errors.log')))) {
                mkdir(substr($sLogFile, 0, strlen($sLogFile) - strlen('/errors.log')));
            }

            fopen($sLogFile, 'w+');
        }
        ini_set('error_log', $sLogFile);

        return;
    }

    /**
     * Parse global config from a ini file
     * @see app/config/
     *
     * @todo mettre en cache
     *
     * @throws AppException
     */
    public static function initConfig()
    {
        if (! File::exists(CONF_PATH . 'config.ini')) {
            throw new AppException('Unable to load core configuration: ' . CONF_PATH . 'config.ini');
        } else {
            // load global app conf
            self::$aConfig = parse_ini_file(CONF_PATH . 'config.ini', true);
        }
    }


    /**
     * Load bundle config if found
     */
    public static function loadBundleConfig()
    {
        if (File::exists(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json')) {
            $sBundleConfig = File::getContent(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json');
            if (! empty($sBundleConfig)) {
                self::$oBundleConfig = new Json($sBundleConfig);
            }
        }
    }

    /**
     * Init router to parse current request
     *
     * @todo passer uniquement un objet abstrait d'une interface avec des accessors à ce niveau pour plus de flexibilité
     * @return array
     */
    public static function initRouter()
    {
        self::$oRouterInstance->init(self::$aConfig);
        return array(
            'bundle' => self::$oRouterInstance->getBundle(),
            'controller' => self::$oRouterInstance->getController(),
            'action' => self::$oRouterInstance->getAction(),
            'params' => self::$oRouterInstance->getParams(),
            'lang' => self::initLocales()
        );
    }

    /**
     * Boostrap app controller
     *
     * @todo /!\ ucfirst risque de bug élévé car ne prend pas en charge le camel case
     */
    private static function initController()
    {

        // @todo ugly but for testing purposes find a better approach or handle a CLI mode
        if (defined('TEST') === true && TEST === true) {
            return;
        }

        $sController = 'bundles\\' . self::$aRequest['bundle'] . '\Controllers\\' . ucfirst( self::$aRequest['controller'] ) . 'Controller';

        if (class_exists($sController)) {
            $oUser = null;
            $oBundleConfig = null;
            if (! is_null(self::$oBundleConfig)) {
                $oBundleConfig = self::$oBundleConfig;
            }
            new $sController($oUser, $oBundleConfig);

        } else {
            // @todo handle 404 errors here (bundle error)
            throw new AppException('No controller found: ' . $sController);
        }
    }

    /**
     * Load locales
     *
     * @todo dirty need refactor also handle country
     *
     * @return string Current local on 2 caracters
     */
    private static function initLocales($sDefaultLocale = 'FR-fr')
    {

        if (strlen(\Locale::getPrimaryLanguage($sDefaultLocale) . '-' . \Locale::getRegion($sDefaultLocale)) > 1) {
            $sDefaultLocale = \Locale::getPrimaryLanguage($sDefaultLocale) . '-' . \Locale::getRegion($sDefaultLocale);
        }

        putenv('LC_ALL=' . $sDefaultLocale . '.' . strtolower(str_replace('-', '', Router::DEFAULT_ENCODING)));
        setlocale(LC_ALL, $sDefaultLocale . '.' . strtolower(str_replace('-', '', Router::DEFAULT_ENCODING)));

        return $sDefaultLocale;
    }

    /**
     * Init current environement under a ENV constant [dev|test|preprod|prod]
     *
     * @see config.ini
     */
    public static function initEnv()
    {
        $sEnv = 'prod';
        
        // Init environments from application configuration
        self::$aEnvironements = self::$aConfig['env'];

        if (
        	isset($_SERVER['SERVER_NAME']) && 
        	in_array($_SERVER['SERVER_NAME'], self::$aEnvironements) && 
        	self::$aConfig['env']['prod'] !== $_SERVER['SERVER_NAME']
    	) {
            $sEnv = 'dev';
        }
        define('ENV', $sEnv);
    }

    /**
     * Register all paths
     *
     * @todo delete and use class constant
     */
    public static function initPaths()
    {
        $sRootPath = substr(
                __DIR__,
                0,
                (strlen(__DIR__) - strlen('Library' . DIRECTORY_SEPARATOR . 'Core'))
            );
        if (defined('ROOT_PATH') === false) {
            define('ROOT_PATH', $sRootPath);
        }
        if (defined('APP_PATH') === false) {
            define('APP_PATH', $sRootPath . 'app/');
        }
        if (defined('CONF_PATH') === false) {
            define('CONF_PATH', $sRootPath . 'app/config/');
        }
        if (defined('LIBRARY_PATH') === false) {
            define('LIBRARY_PATH', $sRootPath . 'Library/');
        }
        if (defined('TMP_PATH') === false) {
            define('TMP_PATH', $sRootPath . 'tmp/');
        }
        if (defined('CACHE_PATH') === false) {
            define('CACHE_PATH', $sRootPath . 'tmp/cache/');
        }
        if (defined('LOG_PATH') === false) {
            define('LOG_PATH', $sRootPath . 'tmp/logs/');
        }
        if (defined('BUNDLES_PATH') === false) {
            define('BUNDLES_PATH', $sRootPath . 'bundles/');
        }
        if (defined('PUBLIC_PATH') === false) {
            define('PUBLIC_PATH', $sRootPath . 'public/');
        }
        if (defined('PUBLIC_BUNDLES_PATH') === false) {
            define('PUBLIC_BUNDLES_PATH', PUBLIC_PATH . 'lib/bundles/');
        }
        if (defined('UX_PATH') === false) {
            define('UX_PATH', PUBLIC_PATH . 'lib/lib/ux/');
        }
    }

    /**
     * Accessors
     */
    public static function getConfig()
    {
        return self::$aConfig;
    }

    public static function setConfig($config)
    {
        self::$aConfig = $config;
    }

    public static function getRequest()
    {
        return self::$aRequest;
    }

    public static function setRequest($request)
    {
        self::$aRequest = $request;
    }

    public static function getPhpVersion()
    {
        return self::$sPhpVersion;
    }

    public static function getAutoloaderInstance()
    {
        return self::$oAutoloaderInstance;
    }

}

class AppException extends \Exception
{
}
