<?php
namespace Library\Core;

use Library\Core\Router;
use bundles\user\Entities\User;

/**
 * App Model class
 * A simple class to build and manage usefull setup informations
 *
 * @dependancy \Library\Core\Cache
 *
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class App
{
    private static $oInstance;

    /**
     * Global app Entities, Mapping and EntitiesCollection default namespaces
     * @var string
     */
    const ENTITIES_NAMESPACE = '\app\Entities\\';
    const ENTITIES_COLLECTION_NAMESPACE = '\app\Entities\Collection\\';
    const MAPPING_ENTITIES_NAMESPACE = '\app\Entities\Mapping\\';

    /**
     * Exceptions error code
     *
     * @var integer
     */
    const ERROR_ENTITY_EXISTS = 400;
    const ERROR_USER_INVALID = 401;
    const ERROR_ENTITY_NOT_LOADED = 402;
    const ERROR_ENTITY_NOT_OWNED_BY_USER = 403;
    const ERROR_ENTITY_NOT_LOADABLE = 404;
    const ERROR_ENTITY_NOT_MAPPED_TO_USERS = 405;
    const ERROR_FORBIDDEN_BY_ACL = 406;

    /**
     * @todo delete??
     * @var App Instance
     */
    private static $oApp;

    /**
     * Router instance
     * @var \Library\Core\Router
     */
    private static $oRouterInstance;

    /**
     * App global configuration parsed from the config.ini file
     *
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
     * Current framework version
     *
     * @var string
     */
    const APP_VERSION = '1.0';

    /**
     * Current framework version
     *
     * @var string
     */
    const APP_STAGING = 'beta';

    /**
     * Current framework release name
     *
     * @var string
     */
    const APP_RELEASE_NAME = 'ihop';

    /**
     * Http request
     *
     * @var array
     */
    private static $aRequest;

    /**
     * Available bundles, controllers and actions
     *
     * @var array
     */
    private static $aBundles;

    /**
     * Available Entities found on the registered Namespace (self::ENTITIES_NAMESPACE)
     *
     * @var array
     */
    private static $aEntities;

    /**
     * Loaded classes for debug
     *
     * @var array
     */
    private static $aLoadedClass = array();

    /**
     * PHP version
     * @var string
     */
    protected static $sPhpVersion;

    /**
     * Instance constructor
     * 
     * @todo refactoriser tout ce code
     * 
     */
    public function __construct()
    {
        // PHP
        // @todo SGBD infos
        self::$sPhpVersion = PHP_VERSION;

        /**
         * @todo en conf
         */
        self::initPaths();        

        /**
         *
         * @see register class autoloader
         */
        self::initAutoloader();        
        
        /**
         * Init config
         */
        self::initConfig();        
        
        /**
         * Init environment staging
         */
        self::initEnv();


        // Init Router component
        self::$oRouterInstance = Router::getInstance();
        
        /**
         *
         * @see Errors and log reporting
         */
        self::initReporting();
        self::initLogs();

        /**
         * Init cache
         */
        self::initCache();

        /**
         * Parse and load bundles, controllers and actions available
         * Read from cache if exists
         */
        $oBundles = new Bundles();
        self::$aBundles = $oBundles->get();

        /**
         * Parse request
         */
        self::$aRequest = self::initRouter();

        /**
         * Load json encoded bundle configuration if found
         */
        self::loadBundleConfig();

        /**
         * Init requested controller
         */
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
        spl_autoload_register('\Library\Core\App::classLoader');
    }

    /**
     * Autoload any class that use namespaces (PSR-4)
     *
     * @param string $sClassName
     */
    public static function classLoader($sClassName)
    {
        $sComponentName = ltrim($sClassName, '\\');
        $sFileName = '';
        $sComponentNamespace = '';
        if ($lastNsPos = strripos($sClassName, '\\')) {
            $sComponentNamespace = substr($sClassName, 0, $lastNsPos);
            $sClassName = substr($sClassName, $lastNsPos + 1);

            $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $sComponentNamespace) . DIRECTORY_SEPARATOR;
        }
        $sFileName .= str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . '.php';

        if (is_file(ROOT_PATH . $sFileName)) {
            self::registerLoadedClass($sClassName, $sComponentNamespace);
            require_once ROOT_PATH . $sFileName;
        }

    }

    /**
     * Init cache based on memcached
     */
    private static function initCache()
    {
        define('CACHE_HOST', self::$aConfig['cache']['host']);
        define('CACHE_PORT', self::$aConfig['cache']['port']);
    }

    /**
     * Init errors and notices reporting
     */
    private static function initReporting()
    {
        // init logs and errors reporting
        error_reporting((ENV === 'dev') ? -1 : 0);
        ini_set('display_errors', (ENV === 'dev') ? 'On' : 'Off');
        ini_set('log_errors', 'On');
    }

    /**
     * Init log file
     */
    private static function initLogs()
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
        if (! Files::exists(CONF_PATH . 'config.ini')) {
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
        if (Files::exists(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json')) {
            $sBundleConfig = Files::getContent(BUNDLES_PATH . self::$oRouterInstance->getBundle() . '/Config/bundle.json');
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
    private static function initRouter()
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
        $sController = 'bundles\\' . self::$aRequest['bundle'] . '\Controllers\\' . ucfirst( self::$aRequest['controller'] ) . 'Controller';

        if (ENV === 'dev') {
            self::registerLoadedClass(Router::getController(), $sController);
        }

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
     * Get available \app\Entities
     *
     * @return array An array on Entities classnames found
     */
    public static function buildEntities()
    {
        // Scan app level entities
        $aAppEntities = Directory::scan(APP_PATH . 'Entities/');
        self::parseEntities($aAppEntities);

        // Scan bundles entities
        foreach (self::$aBundles as $sBundleName=>$aBundleStructure) {
            $aBundleEntities = Directory::scan(BUNDLES_PATH . 'Entities/');
            self::parseEntities($aBundleEntities);
        }

        return self::$aEntities;
    }

    /**
     * Parse entites from a scanned directory (bundles or core app)
     *
     * @param array $aScannedEntities
     */
    private static function parseEntities(array $aScannedEntities = array())
    {
        foreach ($aScannedEntities as $aScannedEntity) {
            if ($aScannedEntity['type'] === 'file') {
                self::$aEntities[] = substr($aScannedEntity['name'], 0, strlen($aScannedEntity['name']) - strlen('.php'));
            } elseif ($aScannedEntity['type'] === 'folder') {
                foreach ($aScannedEntity['items'] as $aEntities) {
                    self::$aEntities[] = substr($aEntities['name'], 0, strlen($aEntities['name']) - strlen('.php'));
                }
            }
        }
    }

    /**
     * Load locales
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
    private static function initEnv()
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
     */
    private static function initPaths()
    {
        // @see paths info
        define('ROOT_PATH',             __DIR__ . '/../../');
        define('APP_PATH',              __DIR__ . '/../../app/');
        define('CONF_PATH',             __DIR__ . '/../../app/config/');
        define('LIBRARY_PATH',          __DIR__ . '/../');
        define('TMP_PATH',              __DIR__ . '/../../tmp/');
        define('CACHE_PATH',            __DIR__ . '/../../tmp/cache/');
        define('LOG_PATH',              __DIR__ . '/../../tmp/logs/');
        define('BUNDLES_PATH',          __DIR__ . '/../../bundles/');
        define('PUBLIC_PATH',           __DIR__ . '/../../public/');
        define('PUBLIC_BUNDLES_PATH',   __DIR__ . '/../../public/lib/bundles/');
        define('UX_PATH',               __DIR__ . '/../../public/lib/ux/');
    }

    /**
     * Register called class for debug purposes
     *
     * @param string $sClassname            Class component name
     * @param string $sComponentPath        Class component path
     */
    public static function registerLoadedClass($sClassname, $sComponentPath = '')
    {
        if (strlen($sClassname) > 0) {
            self::$aLoadedClass[$sClassname . (($sComponentPath != '') ? ' (' . $sComponentPath . ')' : '')] = round(microtime(true) - FRAMEWORK_STARTED, 3);
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

    public static function getLoadedClass()
    {
        return self::$aLoadedClass;
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

    public static function getBundles()
    {
        return self::$aBundles;
    }
}

class AppException extends \Exception
{
}
