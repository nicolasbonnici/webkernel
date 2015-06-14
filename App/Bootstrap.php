<?php
namespace Core\App;

use Core\Router;

/**
 * Boostrap project component
 *
 * @author Nicolas Bonnci <nicolasbonnici@gmail.com>
 *
 */
class Bootstrap
{
    private static $oInstance;

    /**
     * Global app Entities, Mapping and EntityCollection default namespaces
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
     * Router instance
     * @var \Core\Router
     */
    private static $oRouterInstance;

    /**
     * App global configuration parsed from the config.ini file
     *
     * @var array
     */
    private static $oConfig;

    /**
     * An array to store DNS for environment staging
     *
     * @var array
     */
    private static $aEnvironements = array();

    /**
     * Http request
     * @todo Request Component to normalize
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
     * 
     */
    public function __construct()
    {
        // PHP
        // @todo SGBD infos
        self::$sPhpVersion = PHP_VERSION;

        // @see paths info
        define('ROOT_LIBRARY_PATH',     ROOT_PATH . 'Library/');
        define('APP_PATH',              ROOT_PATH . 'app/');
        define('CONF_PATH',             ROOT_PATH . 'app/config/');
        define('TMP_PATH',              ROOT_PATH . 'tmp/');
        define('CACHE_PATH',            ROOT_PATH . 'tmp/cache/');
        define('LOG_PATH',              ROOT_PATH . 'tmp/logs/');
        define('BUNDLES_PATH',          ROOT_PATH . 'bundles/');
        define('PUBLIC_PATH',           ROOT_PATH . 'public/');
        define('PUBLIC_BUNDLES_PATH',   ROOT_PATH . 'public/lib/bundles/');
        define('UX_PATH',               ROOT_PATH . 'public/lib/ux/');

        // Register Autoloader then load project configuration
        require_once BUNDLES_PATH . 'autoload.php';
        $oClassLoader = new \Composer\Autoload\ClassLoader();

        // register classes with namespaces
        $oClassLoader->add('Core', ROOT_LIBRARY_PATH .'/Core');

        // activate the autoloader
        $oClassLoader->register();
        $oConfig = new Configuration();
        die(var_dump($oConfig));

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
         * Init requested controller
         */
        self::initController();
    }

    public static function getInstance()
    {
        if (self::$oInstance instanceof self === false) {
            self::$oInstance = new self();
        }
        return self::$oInstance;
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
        self::parseEntities(APP_PATH . 'Entities/');

        // Scan bundles entities
        foreach (self::$aBundles as $sBundleName=>$aBundleStructure) {
            self::parseEntities(BUNDLES_PATH . $sBundleName . '/Entities/');
        }

        return self::$aEntities;
    }

    /**
     * Parse entites from a given path and subfolders then pass them to the self::$aEntities attribute
     *
     * @param string $sAbsolutePath
     */
    private static function parseEntities($sAbsolutePath)
    {
    	$aFolderContent = Directory::scan($sAbsolutePath);
    	$aExcludedPath = array(
    		'',
    		'Deploy'
    	);
    	
        foreach ($aFolderContent as $aFolderItem) {
            if (
            	$aFolderItem['type'] === 'file' && 
            	is_null($aFolderItem['name']) === false
    		) {
                $sFilename = substr($aFolderItem['name'], 0, strlen($aFolderItem['name']) - strlen('.php'));
            	if (in_array($sFilename, self::$aEntities) === false) {
            		self::$aEntities[] = $sFilename;            		
            	}
            } elseif (
            		$aFolderItem['type'] === 'folder' && 
            		in_array($aFolderItem['name'], $aExcludedPath) === false
    		) {
            	self::parseEntities($sAbsolutePath . $aFolderItem['name']);
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

    public static function getBundles()
    {
        return self::$aBundles;
    }
}

class AppException extends \Exception
{
}
