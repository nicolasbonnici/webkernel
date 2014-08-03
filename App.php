<?php
namespace Library\Core;

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

    /**
     * Entities namespace
     *
     * @var string
     */
    const ENTITIES_NAMESPACE = '\app\Entities\\';
    const MAPPING_ENTITIES_NAMESPACE = '\app\Entities\Mapping\\';
    const ENTITIES_COLLECTION_NAMESPACE = '\app\Entities\Collection\\';

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
     *
     * @var Bootstrap Instance
     */
    private static $oInstance;

    /**
     *
     * @var App Instance
     */
    private static $oApp;

    /**
     * Config multi dimensional array parsed from .
     *
     * ini files
     *
     * @var array
     */
    private static $aConfig;

    /**
     *  Assets managment
     *  @var \Libraries\Core\Assets
     */
    private static $oAssetsInstance;

    /**
     * An array of dns
     *
     * @todo passer en config
     * @var array
     */
    private static $aEnvironements = array(
        'core.local',
        'dev.nbonnici.info'
    );

    /**
     * Current framework version
     *
     * @var string
     */
    const APP_VERSION = '1.0';

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

    protected static $sPhpVersion;

    /**
     * Instance constructor
     */
    public function __construct()
    {
        // PHP
        // @todo SGBD infos
        self::$sPhpVersion = PHP_VERSION;

        /**
         * Init environment staging
         */
        self::initEnv();

        /**
         *
         * @see paths
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
     * Autoload any class that use namespaces
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
        // @ see init logs and errors reporting
        error_reporting((ENV === 'dev') ? - 1 : 0);
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
     * Parse config from a .
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
            self::$aConfig = parse_ini_file(CONF_PATH . 'config.ini', true);
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
        $oRouter = \Library\Core\Router::getInstance();
        $oRouter->init(self::$aConfig);
        return array(
            'bundle' => $oRouter->getBundle(),
            'controller' => $oRouter->getController(),
            'action' => $oRouter->getAction(),
            'params' => $oRouter->getParams(),
            'lang' => self::initLocales()
        );
    }

    /**
     * Boostrap app controller
     *
     * @todo /!\ ucfirst
     */
    private static function initController()
    {
        $sController = 'bundles\\' . \Library\Core\Router::getBundle() . '\Controllers\\' . ucfirst( \Library\Core\Router::getController() ) . 'Controller';

        if (ENV === 'dev') {
            self::registerLoadedClass(\Library\Core\Router::getController(), $sController);
        }

        if (class_exists($sController)) {
            $oUser = null;
            if (isset($_SESSION['iduser']) && intval($_SESSION['iduser'] > 0)) {
                $oUser = new \app\Entities\User(intval($_SESSION['iduser']));
            }

            new $sController($oUser);

        } else {
            throw new AppException('No controller found: ' . $sController);
            // \Library\Core\Router::redirect ( '/' ); // @todo handle 404 errors here (bundle error)
        }
    }

    /**
     * Get available \app\Entities
     *
     * @return array An array on Entities classnames found
     */
    public static function buildEntities()
    {
        $aFolderContent = scandir(ROOT_PATH . 'app/Entities/');
        foreach ($aFolderContent as $sEntity) {
            if ($sEntity !== '.' && $sEntity !== '..' && $sEntity !== 'Collection') {
                self::$aEntities[] = substr($sEntity, 0, strlen($sEntity) - strlen('.php'));
            }
        }
        return self::$aEntities;
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
        if (in_array($_SERVER['SERVER_NAME'], self::$aEnvironements) && self::$aConfig['env']['prod'] !== $_SERVER['SERVER_NAME']) {
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
        define('ENTITIES_PATH',         __DIR__ . '/../../app/Entities/');
        define('ENTITIES_MAPPING_PATH', __DIR__ . '/../../app/Entities/Mapping/');
        define('ENTITIES_DEPLOY_PATH',  __DIR__ . '/../../app/Entities/Deploy/');
        define('ENTITIES_UPDATE_PATH',  __DIR__ . '/../../app/Entities/Update/');
        define('CONF_PATH',             __DIR__ . '/../../app/config/');
        define('LIBRARY_PATH',          __DIR__ . '/../');
        define('TMP_PATH',              __DIR__ . '/../../tmp/');
        define('CACHE_PATH',            __DIR__ . '/../../tmp/cache/');
        define('LOG_PATH',              __DIR__ . '/../../tmp/logs/');
        define('BUNDLES_PATH',          __DIR__ . '/../../bundles/');
        define('PUBLIC_PATH',           __DIR__ . '/../../public/');
        define('PUBLIC_BUNDLES_PATH',   __DIR__ . '/../../public/lib/bundles/');
        define('UX_PATH',               __DIR__ . '/../../public/lib/sociableUx/');
    }


    /**
     * @todo conception
     */
    public static function dump()
    {
        // Export en json
    }

    public static function importFixtures()
    {
        // simple script d import sql
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
