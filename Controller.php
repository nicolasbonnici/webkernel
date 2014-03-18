<?php

namespace Library\Core;

/**
 * Main app controller class
 *
 * @author infradmin
 */
class Controller extends Acl {

    const XHR_STATUS_OK                 = 1;
    const XHR_STATUS_ERROR              = 2;
    const XHR_STATUS_ACCESS_DENIED      = 3;
    const XHR_STATUS_SESSION_EXPIRED    = 4;

    protected $_config;
    protected $_module;
    protected $_controller;
    protected $_action;
    protected $_cookie;
    protected $_session;
    protected $_lang;

    public $_params = array();
    public $_view = array();

    public function __construct($oUser = NULL) {

        $this->_cookie = new \Library\Core\Cookie();

        $this->_config = \Library\Core\Bootstrap::getConfig();
        $this->setSession();
        $this->loadRequest();

        // Check ACL if we have a logged user
        if (
            !is_null($oUser) &&
            $oUser instanceof \app\Entities\User &&
            $oUser->isLoaded()
        ) {
            parent::__construct($oUser);
        }

        // @see run action & pre|post dispatch callback (optionnal)
        if (method_exists($this, $this->_action)) {

            // @see pre dispatch action
            if (method_exists($this, '__preDispatch')) {

                try {
                    $this->__preDispatch();
                } catch (Library\Core\ControllerException $oException) {
                    throw new ControllerException('Pre dispatch action throw an exception: ' . $oException->getMessage(), $oException->getCode());
                    exit;
                }

            }

            // Run mothafucka run!
            $this->{$this->_action}();


            // @see post dispatch action
            if (method_exists($this, '__postDispatch')) {

                try {
                    $this->__postDispatch();
                } catch (Library\Core\ControllerException $oException) {
                    throw new ControllerException('Post dispatch action throw an exception: ' . $oException->getMessage(), $oException->getCode());
                    exit;
                }

            }

        } else {

            throw new ControllerException(__CLASS__ . ' Error cannot find action ' . $this->_action);

        }

        return;
    }

    public function render($sTpl, $iStatusXHR = self::XHR_STATUS_OK , $bToString = false) {

        if (count($this->_params) >0) {
            foreach ($this->_params as $key => $val) {
                    $this->_view[$key]  = $val;
            }
        }

        require_once APP_PATH . '/Translations/' . $this->_lang. '/global.php'; // @see globale translation
        $sTranslationFile = str_replace(".tpl", ".php", $sTpl);
        if (file_exists(MODULES_PATH . '/modules/' . $this->_module . '/Translations/' . $this->_lang . '/' . $sTranslationFile)) {
            require_once MODULES_PATH . '/modules/' . DEFAULT_MODULE . '/translations/' . $this->_lang . '/' . $sTranslationFile;
        }

        if (count($this->_session) > 0) {
            $this->_view['aSession'] = $this->_session;
            // @todo provisoire
            $this->_view['sGravatarSrc16'] = Tools::getGravatar($this->_session['mail'],  16);
            $this->_view['sGravatarSrc32'] = Tools::getGravatar($this->_session['mail'],  32);
            $this->_view['sGravatarSrc64'] = Tools::getGravatar($this->_session['mail'],  64);
            $this->_view['sGravatarSrc128'] = Tools::getGravatar($this->_session['mail'], 128);
        }

        // Translation
        $this->_view["sAppName"] = $this->_config['app']['name'];
        $this->_view["lang"] = $this->_lang;
        $this->_view["tr"] = $tr; // @see loading de la traduction pour la vue

        // Views common couch
        $this->_view["appLayout"] = '../../../app/Views/layout.tpl'; // @todo degager ca ou constante mais quelquechose
        $this->_view["appLoginLayout"] = '../../../app/Views/loginLayout.tpl';
        $this->_view["helpers"] = '../../../app/Views/helpers/';

        // MVC
        $this->_view['sModule'] = $this->_module;
        $this->_view["sController"] = $this->_controller;
        $this->_view["sAction"] = $this->_action;

        // debug
        $this->_view["sEnv"] = ENV;
        $this->_view["aLoadedClass"] = \Library\Core\Bootstrap::getLoadedClass();
        $this->_view["sDeBugHelper"] = '../../../app/Views/helpers/debug.tpl';

        // Benchmark
        $this->_view["render_time"] = microtime(true);
        $this->_view['framework_started'] = FRAMEWORK_STARTED;
        $this->_view['current_timestamp'] = time();
        $this->_view['rendered_time'] = round($this->_view["render_time"] - FRAMEWORK_STARTED, 3);

        // check if it's an XMLHTTPREQUEST
        if($this->isXHR()) {
            //var_dump($this->_view);

            $aResponse = json_encode(array(
                'status'    => $iStatusXHR,
                'content'   => str_replace(array("\r", "\r\n", "\n", "\t"), '', \Library\Core\Bootstrap::initView($sTpl, $this->_view, true)),
                'debug'       => str_replace(array("\r", "\r\n", "\n", "\t"), '', \Library\Core\Bootstrap::initView($this->_view["sDeBugHelper"], $this->_view, true))
            ));
            if ($bToString === true) {
                return $aResponse;
            }

            header('Content-Type: application/json');
            echo $aResponse;
            exit;
        }

        // Render the view using Haanga
        \Library\Core\Bootstrap::initView($sTpl, $this->_view, $bToString);

        return;

    }

    /**
     * Tell if the request is a XmlHttpRequest
     * @return boolean
     */
    protected function isXHR() {
    	return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    protected function loadRequest() {
        $this->_module = Router::getModule();
        $this->_controller = ucfirst(Router::getController()) . 'Controller';
        $this->_action = Router::getAction() . 'Action';;
        $this->_params = Router::getParams();
        $this->_lang = Router::getLang();
    }

    public function getController() {
        return $this->_controller;
    }

    public function setController($controller) {
        $this->_controller = $controller;
    }

    public function getAction() {
        return $this->_action;
    }

    public function setAction($action) {
        $this->_action = $action;
    }

    public function getCookie() {
        return $this->_cookie;
    }

    public function setCookie($cookie) {
        $this->_cookie = $cookie;
    }

    public function getSession() {
        return $this->_session;
    }

    public function setSession() {
        $this->_session = $_SESSION;
    }

    public function getLang() {
        return $this->_lang;
    }

    public function setLang($lang) {
        $this->_lang = $lang;
    }

}

class ControllerException extends \Exception {}

?>
