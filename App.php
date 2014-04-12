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
        $sClassName = ltrim($sClassName, '\\');
        $sFileName = '';
        $namespace = '';
        if ($lastNsPos = strripos($sClassName, '\\')) {
            $namespace = substr($sClassName, 0, $lastNsPos);
            $sClassName = substr($sClassName, $lastNsPos + 1);

            $sFileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $sFileName .= str_replace('_', DIRECTORY_SEPARATOR, $sClassName) . '.php';

        if (is_file(ROOT_PATH . $sFileName)) {
            require_once ROOT_PATH . $sFileName;
        }

        if (ENV === 'dev') {
            self::registerLoadedClass($sFileName);
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
    private static function initConfig()
    {
        if (! Files::exists(CONF_PATH . 'config.ini')) {
            throw new AppException('Unable to load core configuration...');
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
        $oRouter->init();
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
     */
    private static function initController()
    {
        $sController = 'bundles\\' . \Library\Core\Router::getBundle() . '\Controllers\\' . ucfirst(\Library\Core\Router::getController()) . 'Controller';

        if (ENV === 'dev') {
            self::registerLoadedClass($sController);
        }

        if (class_exists($sController)) {
            new $sController();
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
    private static function initLocales()
    {

        /**
         *
         * @see regenerer les locales
         *      find -name *.tpl > totranslate.txt
         *      xgettext -f totranslate.txt -o project.pot
         */
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {

            // @todo intégrer intl à ce niveau
            $sLocale = 'FR_fr';

            if (strlen($sLocale) === 2) {
                $sLocale = strtoupper($sLocale) . '_' . $sLocale;
            }

            $sFilename = Router::DEFAULT_BUNDLE;
            putenv('LC_ALL=' . $sLocale . '.' . strtolower(str_replace('-', '', Router::DEFAULT_ENCODING)));
            setlocale(LC_ALL, $sLocale . '.' . strtolower(str_replace('-', '', Router::DEFAULT_ENCODING)));

            // @see gettext init (on utilise juste des array pour le moment c'est chiant de tout recompiler)
            // bindtextdomain($sFilename, Router::DEFAULT_BUNDLES_PATH . Router::DEFAULT_BUNDLE . '/Translations/');
            //
            // bind_textdomain_codeset($sFilename, Router::DEFAULT_ENCODING);
            // textdomain(Router::DEFAULT_BUNDLE);

            return $sLocale;
        } else {
            throw new AppException('Unable to load locales...');
        }
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
        define('ROOT_PATH', __DIR__ . '/../../');
        define('APP_PATH', __DIR__ . '/../../app/');
        define('CONF_PATH', __DIR__ . '/../../app/config/');
        define('LIBRARY_PATH', __DIR__ . '/../');
        define('TMP_PATH', __DIR__ . '/../../tmp/');
        define('CACHE_PATH', __DIR__ . '/../../tmp/cache/');
        define('LOG_PATH', __DIR__ . '/../../tmp/logs/');
        define('BUNDLES_PATH', __DIR__ . '/../../bundles/');
        define('PUBLIC_PATH', __DIR__ . '/../../public/');
        define('PUBLIC_BUNDLES_PATH', __DIR__ . '/../../public/lib/bundles/');
        define('PUBLIC_BUNDLES_UX_PATH', __DIR__ . '/../../public/lib/sociableUx/');
    }

    /**
     * Return server username
     * @return string
     */
    public static function getServerUsername()
    {
        return exec('whoami');
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

    public static function registerLoadedClass($sClassname)
    {
        if (strlen($sClassname) > 0) {
            self::$aLoadedClass[$sClassname] = round(microtime(true) - FRAMEWORK_STARTED, 3);
        }
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
