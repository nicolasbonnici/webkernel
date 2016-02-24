<?php
namespace Library\Core\Router;

use Library\Core\Bootstrap;
use Library\Core\Exception\CoreException;
use Library\Core\FileSystem\File;
use Library\Core\FileSystem\FileSystem;
use Library\Core\Json\Json;
use Library\Core\Pattern\Singleton;

/**
 * MVC Router component
 *
 * bundle/controller/action/param/value or /customRoute/param/value
 *
 */
class Router extends Singleton
{
    const DEFAULT_CONTROLLER_NAME   = 'home';
    const DEFAULT_ACTION_NAME       = 'index';

    /**
     * Default router settings for frontend
     * @var string
     */
    private static $sDefaultBundle = 'frontend';
    private static $sDefaultController = 'home';
    private static $sDefaultAction = 'index';

    /**
     * Current request url
     * @var string
     */
    private static $sUrl;

    /**
     * Application current bundle loaded
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
     * Request parameters (Application MVC parameters, $_GET, $_POST, $_FILES)
     * @var array
     */
    private static $aParams = array();

    /**
     * Current MVC request
     * @var array
     */
    private static $aRequest = array();

    /**
     * Parsed routes from config
     * @var array
     */
    private static $aRules = array();

    /**
     * Init Router component
     *
     * @param array $aApplicationConf
     */
    public static function init(array $aApplicationConf = array())
    {
        // Load default routing settings
        if (
            isset(
                $aApplicationConf['routing']['default_bundle'],
                $aApplicationConf['routing']['default_controller'],
                $aApplicationConf['routing']['default_action']
            )
        ) {
            self::$sDefaultBundle       = $aApplicationConf['routing']['default_bundle'];
            self::$sDefaultController   = $aApplicationConf['routing']['default_controller'];
            self::$sDefaultAction       = $aApplicationConf['routing']['default_action'];
        }

        self::$sBundle      = self::$sDefaultBundle;
        self::$sController  = self::$sDefaultController;
        self::$sAction      = self::$sDefaultAction;

        // Load custom routes from configuration
        $oRoutesConf = new Json(File::getContent(Bootstrap::getPath(Bootstrap::PATH_APP) . 'config/routes.json'));
        self::$aRules = $oRoutesConf->getAsArray();

        self::$sUrl = $_SERVER['REQUEST_URI'];

        self::$aRequest = self::cleanArray(explode(DIRECTORY_SEPARATOR, self::$sUrl));

        if (is_array(self::$aRequest) && count(self::$aRequest) > 0) {
            // Try to match a custom route or dispatch a basic MVC routing
            self::process();
        }

        // Parse requestion GET, POST and files upload parameters
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

    /**
     * Test if a custom route was requested otherwhise perform a regular MVC routing
     *
     * @return boolean      TRUE if a custom route matched otherwhise false
     */
    private static function process()
    {
        assert('is_array(self::$aRequest) && count(self::$aRequest)>0');

        // Flag if a custom route was founded
        $bRouted = false;

        foreach (self::$aRules as $sUrl => $aRule) {

            // Separte url from parameters
            $aUrl = explode(':', $sUrl);
            if (substr(self::$sUrl, 0, strlen($aUrl[0])) === $aUrl[0]) {

                assert('is_array($aRule)');

                $bRouted = true;
                self::$sBundle = self::$aRules[$sUrl]['bundle'];
                self::$sController = self::$aRules[$sUrl]['controller'];
                self::$sAction = self::$aRules[$sUrl]['action'];

                # Bind from route parameters
                if (isset($aUrl[1]) && count($aRule['params'] > 0)) {
                    $aParsedParameters = array_slice(self::$aRequest, count(self::cleanArray(explode('/', $aUrl[0]))));
                    foreach ($aParsedParameters as $iIndex=>$mParameters) {
                        self::$aParams[$aRule['params'][$iIndex]] = $mParameters;
                    }
                }
                return true;
            }
        }

        // No custom route matched so we proceed with a basic routing treatment
        if ($bRouted === false) {
            return self::dispatch();
        }

        return false;

    }

    /**
     * Perform a basic MVC routing parsing
     */
    private static function dispatch()
    {
        if (($iRequestCount = count(self::$aRequest)) > 0) {

            # Parse request from uri
            if (isset(self::$aRequest[0])) {
                self::$sBundle = self::$aRequest[0];
            }
            if (isset(self::$aRequest[1])) {
                self::$sController = self::$aRequest[1];
            }
            if (isset(self::$aRequest[2])) {
                self::$sAction = self::$aRequest[2];
            }

            # Parse MVC parameters from url
            self::setParams(array_slice(self::$aRequest, 3));

            # SEO duplicate content friendly -.-
            if (
                isset(self::$aRequest[0]) === true &&
                empty(self::$aRequest[0]) === false &&
                isset(self::$aRequest[1]) === true &&
                empty(self::$aRequest[1]) === false &&
                self::getController() === self::getDefaultController() &&
                self::getAction() === self::getDefaultAction()
            ) {
                if (self::getBundle() === self::getDefaultBundle()) {
                    # Redirect to root domain
                    self::redirect(FileSystem::DS);
                } else {
                    # Redirect to bundle root
                    self::redirect(FileSystem::DS . self::getBundle());
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Parse MVC parameters from requested URI
     *
     * @param array $aArray
     * @return array
     */
    private static function cleanArray(array $aArray = array())
    {
        if (count($aArray) > 0) {
            foreach ($aArray as $key => $sValue) {
                if (empty($sValue) === true) {
                    unset($aArray[$key]);
                }
            }
        }
        return array_values($aArray);
    }

    /**
     * Set MVC parameters from requested uri
     *
     * @param array $items
     * @return array
     */
    private static function setParams(array $items)
    {
        if (empty($items) === false) {
            for ($i = 0; $i < count($items); $i ++) {
                if ($i % 2 === 0) {
                    self::$aParams[$items[$i]] = $items[$i + 1];
                }
            }
        }
        return self::$aParams;
    }

    public static function redirect($sUrl)
    {
        if (is_null($sUrl) === true || empty($sUrl) === true) {
            header('Location: /');
        }
        header('Location: ' . $sUrl);
        exit;
    }

    /**
     * Accessors
     */

    public static function getDefaultBundle()
    {
        return self::$sDefaultBundle;
    }

    public static function getDefaultController()
    {
        return self::$sDefaultController;
    }


    public static function getDefaultAction()
    {
        return self::$sDefaultAction;
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

}

class RouterException extends CoreException
{
}
