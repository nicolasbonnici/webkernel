<?php

namespace Library\Core;

/**
 * MVC basic router
 *
 * module/controler/action/:param
 */
class Router extends Singleton {

    static protected $sLang;
    static protected $sModule;
    static protected $sController;
    static protected $sAction;
    static protected $aParams;
    static protected $sUrl;
    static protected $aRequest;

    static protected $aRules = array();

    public static function init() {

        // @todo retrieve from db
        self::$aRules = array(
                '/login' => array(
                        'module'    => 'frontend',
                        'controller' => 'auth',
                        'action'    => 'index'
                ),
                '/logout' => array(
                        'module'    => 'frontend',
                        'controller' => 'auth',
                        'action'    => 'logout'
                ),
                '/profile' => array(
                        'module'    => 'backend',
                        'controller' => 'user',
                        'action'    => 'profile'
                ),
                '/portfolio' => array(
                        'module'    => 'frontend',
                        'controller' => 'home',
                        'action'    => 'portfolio'
                ),
                '/lifestream' => array(
                        'module'    => 'frontend',
                        'controller' => 'home',
                        'action'    => 'lifestream'
                ),
                '/contact' => array(
                        'module'    => 'frontend',
                        'controller' => 'home',
                        'action'    => 'contact'
                ),
                '/lifestream' => array(
                        'module'    => 'backend',
                        'controller' => 'lifestream',
                        'action'    => 'index'
                ),
                '/blog' => array(
                        'module'    => 'backend',
                        'controller' => 'blog',
                        'action'    => 'index'
                ),
                '/todo' => array(
                        'module'        => 'backend',
                        'controller'     => 'todo',
                        'action'        => 'index'
                )
        );

        self::$sUrl = $_SERVER['REQUEST_URI'];

        self::$aRequest = self::cleanArray(explode('/', self::$sUrl));        // @todo move function cleanArray to toolbox

        self::$sLang = DEFAULT_LANG;
        self::$sModule = DEFAULT_MODULE;
        self::$sController = DEFAULT_CONTROLLER;
        self::$sAction = DEFAULT_ACTION;

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

                self::$sModule = self::$aRules[$sUrl]['module'];
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
                    self::$sModule = self::$aRequest[0];
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

    /**
     * Simple redirection abstraction layer
     *
     * @param mixed array|string $mUrl
     * @todo handle router request
     */
    public static function redirect($mUrl) {
        assert('is_string($mUrl) || is_array($mUrl)');

        if (is_string($mUrl)) {
            header('Location: ' . $mUrl );
            exit();
        } elseif (is_array($mUrl)) {
            if (
                    array_key_exists('request', $mUrl) &&
                    isset(
                            $mUrl['request']['module'],
                            $mUrl['request']['controller'],
                            $mUrl['request']['action']
                    )
             ) {
                self::$sUrl = '/' . $mUrl['request']['module'] . '/' .$mUrl['request']['controller'] . '/' . $mUrl['request']['action'];
            } else {
                throw new RouterException(__METHOD__ . ' malformed redirection request  ');
            }

            header('Location: ' .  self::$sUrl);
        } else {

            throw new RouterException(__METHOD__ . ' wrong request data type (mixed string|array)  ');
        }

        return;
    }

    public static function getModule() {
        return self::$sModule;
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
