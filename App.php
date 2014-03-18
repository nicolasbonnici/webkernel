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

class App extends Singleton {

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
     * Available modules, controllers and actions
     *
     * @var array
     */
    private static $aModules;

    /**
     * \Library\Core\Bootstrap::$aModules \Library\Core\Cache duration in seconds
     *
     * @var integer
     */
    protected static $iModulesCacheDuration = 1314000;


    protected static $sPhpVersion;

    public function __construct()
    {
        /**
         * Parse and load modules, controllers and actions available
         */
        self::buildModules();

        self::$sPhpVersion = PHP_VERSION;
        // @todo SGBD infos

    }

    /**
     * Get an array of all app modules
     *
     * @return array                        A three dimensional array that contain each module along with his own controllers and methods (actions only)
     */
    public static function buildModules()
    {
        assert('is_dir(MODULES_PATH)');
        $aParsedModules = array();

        self::$aModules = \Library\Core\Cache::get(\Library\Core\Cache::getKey(get_called_class(), 'aAppModulesTree'));
        if (self::$aModules === false) {
            $aModules = array_diff(scandir(MODULES_PATH), array('..', '.'));
            foreach($aModules as $sModule) {
                $aParsedModules[$sModule] = self::buildControllers($sModule);
            }
            self::$aModules = $aParsedModules;
            Cache::set(\Library\Core\Cache::getKey(get_called_class(), 'aAppModulesTree'), $aParsedModules, false, self::$iModulesCacheDuration);
        }
    }

    /*
     * Get an array of all Controllers  and methods for a given module
     *
     * @param string $sModule               The module name
     * @return array                        A two dimensional array that contain each controller from a module along with his own methods (actions only)
     */
    public static function buildControllers($sModule)
    {

        assert('!empty($sModule) && is_string($sModule) && is_dir(MODULES_PATH . "/" . $sModule . "/Controllers/")');

        $aControllers = array();
        $sControllerPath = MODULES_PATH . '/' . $sModule . '/Controllers/';
        $aFiles = array_diff(scandir($sControllerPath), array('..', '.'));

        foreach ($aFiles as $sController) {
            if (preg_match('#Controller.php$#', $sController)) {
                $aControllers[substr($sController, 0, strlen($sController) - strlen('Controller.php'))] = self::buildActions($sModule, $sController);
            }
        }

        return $aControllers;
    }

    /**
     * Get all actions from a given module and controller (this method only return [foo]Action() methods)
     *
     * @param string $sModule               The module name
     * @param string $sController           The controller name to parse
     * @return array                        A two dimensional array with the controllers and their methods (actions only)
     */
    public static function buildActions($sModule, $sController)
    {

        assert('!empty($sController) && is_string($sController) && !empty($sModule) && is_string($sModule)');
        $aActions = array();
        $aMethods = get_class_methods('\bundles\\' . $sModule . '\Controllers\\' . substr($sController, 0, strlen($sController) - strlen('.php')));
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