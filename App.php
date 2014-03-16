<?php

namespace Library\Core;

/**
 * App Model class
 * A simple class to build and manage usefull setup informations
 *
 * @author niko
 *
 */

class App {

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

    protected $sPhpVersion;

    public function __construct()
    {
        $this->sPhpVersion = PHP_VERSION;
    }

    /**
     * Get an array of all app modules
     *
     * @return array                        A three dimensional array that contain each module along with his own controllers and methods (actions only)
     */
    public function buildModules()
    {

        assert('is_dir(MODULES_PATH)');

        $aMenu = array();
        $aModules = array_diff(scandir(MODULES_PATH), array('..', '.'));

        foreach($aModules as $sModule) {
            $aMenu[$sModule] = $this->buildControllers($sModule);
        }

        return $aMenu;
    }

    /*
     * Get an array of all Controllers  and methods for a given module
     *
     * @param string $sModule               The module name
     * @return array                        A two dimensional array that contain each controller from a module along with his own methods (actions only)
     */
    public function buildControllers($sModule)
    {

        assert('!empty($sModule) && is_string($sModule) && is_dir(MODULES_PATH . "/" . $this->_module . "/Controllers/")');

        $aControllers = array();
        $sControllerPath = MODULES_PATH . '/' . $sModule . '/Controllers/';
        $aFiles = array_diff(scandir($sControllerPath), array('..', '.'));

        foreach ($aFiles as $sController) {
            if (preg_match('#Controller.php$#', $sController)) {
                $aControllers[substr($sController, 0, strlen($sController) - strlen('Controller.php'))] = $this->buildActions($sModule, $sController);
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
    public function buildActions($sModule, $sController)
    {

        assert('!empty($sController) && is_string($sController) && !empty($sModule) && is_string($sModule)');
        $aActions = array();
        $aMethods = get_class_methods('\modules\\' . $sModule . '\Controllers\\' . substr($sController, 0, strlen($sController) - strlen('.php')));
        if (count($aMethods) > 0) {
            foreach ($aMethods as $sMethod) {
                if (preg_match('#Action$#', $sMethod) && $sMethod !== 'getAction' && $sMethod !== 'setAction') {
                    $aActions[] = substr($sMethod, 0, strlen($sMethod) - strlen('Action'));
                }
            }
        }

        return $aActions;
    }

    public function getPhpVersion()
    {
        return $this->sPhpVersion;
    }
}