<?php
namespace Library\Core;

/**
 * MVC basic router
 *
 * bundle/controler/action/:param
 */
class Router extends Singleton
{

    /**
     * Locales
     *
     * @todo config.ini
     * @var string
     */
    const DEFAULT_ENCODING = 'UTF-8';

    const DEFAULT_LOCALE = 'FR_fr';

    /**
     * Default router settings for frontend
     *
     * @var string
     */
    private static $sDefaultBundle = 'frontend';
    private static $sDefaultController = 'home';
    private static $sDefaultAction = 'index';

    /**
     * Default router settings for backend
     * @var string
     */
    private static $sDefaultBackendBundle = 'backend';
    private static $sDefaultBackendAction = 'index';
    private static $sDefaultBackendController = 'home';

    /**
     * Application language formatted like FR_fr (COUNTRY_langage)
     * @var string
     */
    private static $sLang;

    /**
     * Apllication current bundle loaded
     * @var string
     */
    private static $sBundle;

    /**
     * Application current controller
     * @var string
     */
    private static $sController;

    /**
     * Application current action
     * @var string
     */
    private static $sAction;

    /**
     * Request parameters (Application MVC parameters, $_GET, $_POST, $_FILES) passed to the controller
     * @var array
     */
    private static $aParams;

    /**
     * Current request url
     * @var string
     */
    private static $sUrl;

    /**
     * Current MVC request
     * @var array
     */
    private static $aRequest;

    private static $aRules = array();

    public static function init(array $aApplicationConf = array())
    {
            // Load default routing setting
        if (
            isset(
                $aApplicationConf['default_bundle'],
                $aApplicationConf['default_controller'],
                $aApplicationConf['default_action']
            ) && (
                $aApplicationConf['default_bundle']     !== self::$sDefaultBundle ||
                $aApplicationConf['default_controller'] !== self::$sDefaultController ||
                $aApplicationConf['default_action']     !== self::$sDefaultAction
            )
        ) {
            self::$sDefaultBundle       = $aApplicationConf['default_bundle'];
            self::$sDefaultController   = $aApplicationConf['default_controller'];
            self::$sDefaultAction       = $aApplicationConf['default_action'];
        }
        if (
            isset(
                $aApplicationConf['default_backend_bundle'],
                $aApplicationConf['default_backend_controller'],
                $aApplicationConf['default_backend_action']
            ) && (
                $aApplicationConf['default_backend_bundle']     !== self::$sDefaultBackendBundle ||
                $aApplicationConf['default_backend_controller'] !== self::$sDefaultBackendController ||
                $aApplicationConf['default_backend_action']     !== self::$sDefaultBackendAction
            )
        ) {
            self::$sDefaultBackendBundle     = $aApplicationConf['default_backend_bundle'];
            self::$sDefaultBackendController = $aApplicationConf['default_backend_controller'];
            self::$sDefaultBackendAction     = $aApplicationConf['default_backend_action'];
        }

        self::$aRules = $oRoutesConf->getAsArray();
        // Load custom routes from configuration
        $oRoutesConf = new Json(Files::getContent(CONF_PATH . 'routes.json'));
        self::$aRules = $oRoutesConf->getAsArray();

        self::$sUrl = $_SERVER['REQUEST_URI'];

        self::$aRequest = self::cleanArray(explode('/', self::$sUrl)); // @todo move function cleanArray to toolbox

        self::$sLang = self::DEFAULT_LOCALE;

        if (is_array(self::$aRequest) && count(self::$aRequest) > 0) {
            // Test custom routing here
            self::matchRules();
        }

        foreach ($_FILES as $key => $value) {
            self::$aParams[$key] = $value;
        }

        foreach ($_POST as $key => $value) {
            self::$aParams[$key] = $value;
        }

        foreach ($_GET as $key => $value) {
            self::$aParams[$key] = $value;
        }
    }

    private static function matchRules()
    {
        assert('is_array(self::$aRequest) && count(self::$aRequest)>0');

        // @see flag custom route found
        $bRouted = false;

        foreach (self::$aRules as $sUrl => $aRule) {

            // @see custom routing rule match with request
            $aUrl = explode(':', $sUrl);
            if (preg_match('#^/' . self::$aRequest[0] . '#', $aUrl[0])) {

                assert('is_array($aRule)');

                $bRouted = false;

                self::$sBundle = self::$aRules[$sUrl]['bundle'];
                self::$sController = self::$aRules[$sUrl]['controller'];
                self::$sAction = self::$aRules[$sUrl]['action'];
                if (($aParams = array_slice(self::$aRequest, count(self::cleanArray(explode('/', $aUrl[0]))))) && count($aParams) > 0) {
                    self::setParams($aParams);
                }

                return;
            }
        }
        if (! $bRouted) {
            // @see no custom route matched so we proceed with a basic routing treatment
            if (($iRequestCount = count(self::$aRequest)) > 0) {
                // @todo optimiser ce traitement
                if (isset(self::$aRequest[0])) {
                    self::$sBundle = self::$aRequest[0];
                }

                if (isset(self::$aRequest[1])) {
                    self::$sController = self::$aRequest[1];
                }

                if (isset(self::$aRequest[2])) {
                    self::$sAction = self::$aRequest[2];
                }

                self::setParams(array_slice(self::$aRequest, 3));
            }
        }

        return;
    }

    // @todo c'est deguelasse y'a array_values pour faire ce genre de traitement
    private static function cleanArray(array $aArray = array())
    {
        if (count($aArray) > 0) {
            foreach ($aArray as $key => $sValue) {
                if (! strlen($sValue)) {
                    unset($aArray[$key]);
                }
            }
        }
        return array_values($aArray);
    }

    /**
     * Parse parameters from request url
     *
     * @param array $items
     * @return array
     */
    private static function setParams(array $items)
    {
        if ((! empty($items)) && (count($items) % 2 == 0)) {
            for ($i = 0; $i < count($items); $i ++) {
                if ($i % 2 == 0) {
                    self::$aParams[$items[$i]] = $items[$i + 1];
                }
            }
        }
        return self::$aParams;
    }

    /**
     * Accessors
     */

    public static function getDefaultBundle()
    {
        return self::$sDefaultBundle;
    }

    public static function getDefaultBackendBundle()
    {
        return self::$sDefaultBackendBundle;
    }

    public static function getDefaultController()
    {
        return self::$sDefaultController;
    }

    public static function getDefaultBackendController()
    {
        return self::$sDefaultBackendController;
    }

    public static function getDefaultAction()
    {
        return self::$sDefaultAction;
    }

    public static function getDefaultBackendAction()
    {
        return self::$sDefaultBackendAction;
    }

    public static function getBundle()
    {
        return self::$sBundle;
    }

    public static function getController()
    {
        return self::$sController;
    }

    public static function getAction()
    {
        return self::$sAction;
    }

    public static function getParams()
    {
        return self::$aParams;
    }

    public static function getParam($id)
    {
        return self::$aParams[$id];
    }

    public static function getLang()
    {
        return self::$sLang;
    }
}

class RouterException extends \Exception
{
}
