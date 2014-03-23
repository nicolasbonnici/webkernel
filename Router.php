<?php

namespace Library\Core;

/**
 * MVC basic router
 *
 * bundle/controler/action/:param
 */
class Router extends Singleton {

    /**
     * Locales
     * @todo config.ini
     * @var string
     */
    const DEFAULT_ENCODING  = 'UTF-8';
    const DEFAULT_LOCALE    = 'FR_fr';

    /**
     * Default config
     * @todo setup and load from app config.ini
     * @var string
     */
    const DEFAULT_BUNDLE            = 'frontend';
    const DEFAULT_BACKEND_BUNDLE    = 'lifestream';
    const DEFAULT_CONTROLLER        = 'home';
    const DEFAULT_ACTION            = 'index';

    static protected $sLang;
    static protected $sBundle;
    static protected $sController;
    static protected $sAction;
    static protected $aParams;
    static protected $sUrl;
    static protected $aRequest;

    static protected $aRules = array();

    public static function init() {

        // @todo retrieve from db/config/overwrite
        self::$aRules = array(
                '/login' => array(
                        'bundle'    => 'auth',
                        'controller' => 'home',
                        'action'    => 'index'
                ),
                '/logout' => array(
                        'bundle'    => 'auth',
                        'controller' => 'logout',
                        'action'    => 'index'
                ),
                '/profile' => array(
                        'bundle'    => 'user',
                        'controller' => 'home',
                        'action'    => 'profile'
                ),
                '/portfolio' => array(
                        'bundle'    => 'frontend',
                        'controller' => 'home',
                        'action'    => 'portfolio'
                ),
                '/a-propos' => array(
                        'bundle'    => 'frontend',
                        'controller' => 'home',
                        'action'    => 'about'
                ),
                '/lifestream' => array(
                        'bundle'    => 'lifestream',
                        'controller' => 'home',
                        'action'    => 'index'
                )
//                 '/blog' => array(
//                         'bundle'    => 'blog',
//                         'controller' => 'home',
//                         'action'    => 'index'
//                 ),
//                 '/todo' => array(
//                         'bundle'        => 'todo',
//                         'controller'     => 'home',
//                         'action'        => 'index'
//                 )
        );

        self::$sUrl = $_SERVER['REQUEST_URI'];

        self::$aRequest = self::cleanArray(explode('/', self::$sUrl));        // @todo move function cleanArray to toolbox

        self::$sLang = self::DEFAULT_LOCALE;
        self::$sBundle = self::DEFAULT_BUNDLE;
        self::$sController = self::DEFAULT_CONTROLLER;
        self::$sAction = self::DEFAULT_ACTION;

        if (is_array(self::$aRequest) && count(self::$aRequest) > 0) {

            // Test custom routing here
            self::matchRules();

        }

        foreach($_FILES as $key=>$value) {
            self::$aParams[$key] = $value;
        }

        foreach($_POST as $key=>$value) {
            self::$aParams[$key] = $value;
        }

        foreach($_GET as $key=>$value) {
            self::$aParams[$key] = $value;
        }

    }


    private static function matchRules() {

        assert('is_array(self::$aRequest) && count(self::$aRequest)>0');

        // @see flag cstom route found
        $bRouted = false;

        foreach (self::$aRules as $sUrl=>$aRule) {

            // @see custom routing rule match with request
            $aUrl = explode(':', $sUrl);
            if (preg_match('#^/' . self::$aRequest[0] . '#', $aUrl[0])) {

                assert('is_array($aRule)');

                $bRouted = false;

                self::$sBundle = self::$aRules[$sUrl]['bundle'];
                self::$sController = self::$aRules[$sUrl]['controller'];
                self::$sAction = self::$aRules[$sUrl]['action'];
                if (
                    ($aParams = array_slice(self::$aRequest, count(self::cleanArray(explode('/', $aUrl[0])))))
                    && count($aParams) > 0
                ) {
                    self::setParams($aParams);
                }

                return;

            }

        }
        if (!$bRouted) {
            // @see no custom route matched so we proceed with a basic routing treatment
            if (($iRequestCount = count(self::$aRequest)) > 0) {
                // @todo optimiser ce traitement
                if (isset(self::$aRequest[0])) {
                    self::$sBundle = self::$aRequest[0];
                }

                if (isset(self::$aRequest[1])) {
                    self::$sController = self::$aRequest[1];
                }

                if(isset(self::$aRequest[2])) {
                    self::$sAction = self::$aRequest[2];
                }

                self::setParams(array_slice(self::$aRequest, 3));

            }
        }

        return;
    }

    // @todo c'est deguelasse y'a array_values pour faire ce genre de traitement
    private static function cleanArray(array $aArray = array()) {
        if (count($aArray) > 0) {
            foreach ($aArray as $key=>$sValue) {
                if (!strlen($sValue)) {
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
    private static function setParams(array $items) {
        if ((!empty($items)) && (count($items) % 2 == 0)) {
            for ($i = 0; $i < count($items); $i++) {
                if ($i % 2 == 0) {
                    self::$aParams[$items[$i]] = $items[$i + 1];
                }
            }
        }
        return self::$aParams;
    }

    public static function getBundle() {
        return self::$sBundle;
    }

    public static function getController() {
        return self::$sController;
    }

    public static function getAction() {
        return self::$sAction;
    }

    public static function getParams() {
        return self::$aParams;
    }

    public static function getParam($id) {
        return self::$aParams[$id];
    }

    public static function getLang() {
        return self::$sLang;
    }

}

class RouterException extends \Exception {}
