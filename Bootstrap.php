<?php
namespace Library\Core;

use Library\Core\Http\Headers;

/**
 * That class bootstrap project
 *
 * Class Bootstrap
 * @package Library\Core
 */
class Bootstrap
{
    /**
     * Project global configuration path (relative from the detected project root path)
     */
    const PROJECT_GLOBAL_CONFIGURATION = 'app/config/config.ini';

    /**
     * Project configuration keys
     * @var string
     */
    const CONFIG_APP        = 'app';
    const CONFIG_ROUTER     = 'routing';
    const CONFIG_ENV        = 'env';
    const CONFIG_DATABASE   = 'database';
    const CONFIG_PATH       = 'path';
    // @todo Move this one under the lifestream bundle
    const CONFIG_SOCIAL     = 'social';
    const CONFIG_CACHE      = 'cache';
    const CONFIG_SUPPORT    = 'support';

    /**
     * Project configuration key for path
     * @var string
     */
    const PATH_BUNDLES              = 'bundles';
    const PATH_APP                  = 'app';
    const PATH_CONFIG               = 'app_config';
    const PATH_LIBRARY              = 'library';
    const PATH_WIDGETS              = 'widgets';
    const PATH_PUBLIC               = 'public';
    const PATH_PUBLIC_ASSETS        = 'public_assets';
    const PATH_PUBLIC_BUNDLE_ASSETS = 'public_assets_bundles';
    const PATH_TMP                  = 'tmp';
    const PATH_TMP_CACHE            = 'tmp_cache';
    const PATH_TMP_LOGS             = 'tmp_logs';

    /**
     * Project default localization settings
     */
    const DEFAULT_COUNTRY        = 'FR';
    const DEFAULT_LANG           = 'fr';
    const COUNTRY_LANG_SEPARATOR = '_';
    const DEFAULT_COUNTRY_LANG   = 'FR_fr';
    /** Add in configuration file config.ini */
    const DEFAULT_DATE_FORMAT    = 'd/m/Y H:i:s';

    /**
     * Request locale parameter
     * @var string
     */
    protected static $sLocale = '';

    /**
     * Available translation
     * @var array
     */
    protected static $aSupportedCountries = array(
        'us' => 'EN',
        'en' => 'EN',
        'fr' => 'FR'
    );


    /**
     * Bootstrap instance
     *
     * @var Bootstrap
     */
    private static $oInstance;

    /**
     * Class Autoloader instance
     *
     * @var \Library\Core\Autoload
     */
    protected static $oAutoloaderInstance;

    /**
     * Router instance
     *
     * @var \Library\Core\Router\Router
     */
    private static $oRouterInstance;

    /**
     * Project global configuration
     * @var array
     */
    private static $aConfig;

    /**
     * An array of dns
     *
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
     * Parsed paths from configuration
     *
     * @var array
     */
    protected static $aProjectPaths = array(
        self::PATH_APP => '',
        self::PATH_CONFIG => '',
        self::PATH_LIBRARY => '',
        self::PATH_BUNDLES => '',
        self::PATH_BUNDLES => '',
        self::PATH_WIDGETS => '',
        self::PATH_PUBLIC => '',
        self::PATH_PUBLIC_ASSETS => '',
        self::PATH_PUBLIC_BUNDLE_ASSETS  => '',
        self::PATH_TMP => '',
        self::PATH_TMP_CACHE => '',
        self::PATH_TMP_LOGS => ''
    );

    /**
     * PHP version
     * @var string
     */
    protected static $sPhpVersion;

    /**
     * Project absolute path
     *
     * @var string
     */
    protected static $sRootPath = '';

    /**
     * Instance constructor
     */
    public function __construct()
    {
        // Grab microtime for benchmark purposes
        define('FRAMEWORK_STARTED', microtime(true));

        # PHP version
        self::$sPhpVersion = PHP_VERSION;

        # Detect project absolute root path
        self::initRootPath();

        # Init Autoload component
        if (self::initAutoloader() === false) {
            throw new BootstrapException('Unable to initialize Autoload component');
        }

        # Parse project configuration
        self::initConfig();        

        # Init project staging
        self::initEnv();

        # Init Router component
        self::$oRouterInstance = \Library\Core\Router\Router::getInstance();
        self::$aRequest = self::initRouter();

        # Init error reporting according to project staging environment
        self::initReporting();

        # Init project logs
        self::initLogs();

        # Init project cache engine
        self::initCache();

        # Bootstrap the requested Controller
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
        require self::$sRootPath . 'Library/Core/Autoload.php';
        self::$oAutoloaderInstance = new Autoload();

        return self::$oAutoloaderInstance->register();
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
     *
     * @todo constant on 'error.log'
     */
    public static function initLogs()
    {
        $sLogFile = Bootstrap::getPath(Bootstrap::PATH_TMP_LOGS) . '/errors.log';
        if (! is_file($sLogFile)) {

            if (! is_dir(Bootstrap::getPath(Bootstrap::PATH_TMP_LOGS))) {
                mkdir(Bootstrap::getPath(Bootstrap::PATH_TMP_LOGS));
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
     *
     * @throws BootstrapException
     */
    public static function initConfig()
    {
        if (\Library\Core\FileSystem\File::exists(self::$sRootPath . self::PROJECT_GLOBAL_CONFIGURATION) === false) {
            throw new BootstrapException('Unable to load core configuration: ' . self::$sRootPath . self::PROJECT_GLOBAL_CONFIGURATION);
        } else {
            # Load global app conf
            self::$aConfig = parse_ini_file(self::$sRootPath . self::PROJECT_GLOBAL_CONFIGURATION, true);

            # Compute all projects path from parsed configuration
            if (isset(self::$aConfig[self::CONFIG_PATH]) === true) {
                foreach (self::$aProjectPaths as $sConfKey => $sEmptyValue) {
                    if (isset(self::$aConfig[self::CONFIG_PATH][$sConfKey]) === true) {
                        self::$aProjectPaths[$sConfKey] = self::$aConfig[self::CONFIG_PATH][$sConfKey];
                    }
                }

            }
        }
    }

    /**
     * Init router to parse current request
     *
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
     * Bootstrap the requested Controller
     *
     * @throws BootstrapException
     */
    private static function initController()
    {

        // @todo ugly but for testing purposes find a better approach or handle a CLI mode
        if (defined('TEST') === true && TEST === true) {
            return;
        }

        // @todo How to handle a BlogDashBoardController name with ucfirst?? buggy and ugly
        $sController = 'bundles\\' . self::$aRequest['bundle'] . '\Controllers\\' . ucfirst( self::$aRequest['controller'] ) . 'Controller';

        if (class_exists($sController)) {
            # No User instance at this level only for Auth component
            $oUser = null;
            new $sController($oUser);

        } else {

            /**
             * @todo handle properly the 404 error and root if available to the error bundle
             */
            $oHttpHeader = new Headers();
            $oHttpHeader->setStatus(404);
            $oHttpHeader->sendHeaders();
            exit;

        }
    }

    /**
     * Load locales
     *
     * @return string Current local on 2 characters
     */
    private static function initLocales(
        $sDefaultLocale = self::DEFAULT_COUNTRY_LANG,
        $sCountryLangSeparator = self::COUNTRY_LANG_SEPARATOR
    )
    {
        # Retrieve default browser language
        $aUserAcceptedLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $sLang = array_shift($aUserAcceptedLanguages);
        if (in_array($sLang, self::$aSupportedCountries) === true) {
            $sDefaultLocale = self::$aSupportedCountries[$sLang] . $sCountryLangSeparator . \Locale::getPrimaryLanguage($sLang);
        }

        # test for $sDefaultLocale value
        if (preg_match('/^[A-Z]{2}_{1}[a-z]{2}$/', $sDefaultLocale)) {
            $sDefaultLocale = \Locale::getRegion($sDefaultLocale) . $sCountryLangSeparator . \Locale::getPrimaryLanguage($sDefaultLocale);
        }

        # Assign for instance
        self::$sLocale = $sDefaultLocale;

        return $sDefaultLocale;
    }

    /**
     * Init current environment under a ENV constant [dev|test|preprod|prod]
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
     * Detect and store the project absolute path under instance
     */
    public static function initRootPath()
    {
        self::$sRootPath = substr(
            __DIR__,
            0,
            (strlen(__DIR__) - strlen('Library' . DIRECTORY_SEPARATOR . 'Core'))
        );

    }

    /**
     * Get the project absolute root path
     *
     * @return string
     */
    public static function getRootPath()
    {
        return self::$sRootPath;
    }

    /**
     * Generic project paths accessor
     *
     * @param string $sFolder
     * @return string               The full absolute computed path or null
     */
    public static function getPath($sFolder = null)
    {
        return (is_null($sFolder) === false && array_key_exists($sFolder, self::$aProjectPaths) === true)
            ? self::$sRootPath . self::$aProjectPaths[$sFolder] . DIRECTORY_SEPARATOR
            : null;
    }

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

    /**
     * Get the current locales country_lang FR_fr
     * @return string
     */
    public static function getLocale()
    {
        return self::$sLocale;
    }

}

class BootstrapException extends \Exception
{}
