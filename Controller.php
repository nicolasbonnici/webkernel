<?php
namespace Library\Core;

/**
 * Main app controller class
 *
 * @author infradmin
 */
class Controller extends Acl
{

    /**
     * Errors codes
     *
     * @var interger
     */
    const XHR_STATUS_OK = 1;

    const XHR_STATUS_ERROR = 2;

    const XHR_STATUS_ACCESS_DENIED = 3;

    const XHR_STATUS_SESSION_EXPIRED = 4;

    /**
     * Current locale "[COUNTRY]_[LANGUAGE]"
     * @var string
     */
    protected $sLang;

    /**
     * Requested bundle
     * @var string
     */
    protected $sBundle;

    /**
     * Requested controller
     * @var string
     */
    protected $sController;

    /**
     * Requested action
     * @var string
     */
    protected $sAction;

    /**
     * Current request parameters
     * @var array
     */
    public $aParams = array();

    /**
     * Configuration parsed from .ini|.yaml
     *
     * @var array
     */
    protected $aConfig;

    /**
     * Current cookie
     * @var \Library\Core\Cookie
     */
    protected $oCookie;

    /**
     * Current PHP session
     * @var array
     */
    protected $aSession;

    /**
     * Rendering engine current instance view parameters
     * @see \Library\Haanga\
     * @var array
     */
    public $aView = array();

    public function __construct($oUser = NULL)
    {
        $this->aConfig = \Library\Core\App::getConfig();
        $this->setSession();
        $this->loadRequest();

        // Check ACL if we have a logged user
        if (! is_null($oUser) && $oUser instanceof \app\Entities\User && $oUser->isLoaded()) {
            parent::__construct($oUser);
        }

        // @see run action & pre|post dispatch callback (optionnal)
        if (method_exists($this, $this->sAction)) {

            // Load session
            if (count($this->aSession) > 0) {
                $this->aView['aSession'] = $this->aSession;

                // @todo provisoire
                $this->aView['sGravatarSrc16'] = Tools::getGravatar($this->aSession['mail'], 16);
                $this->aView['sGravatarSrc32'] = Tools::getGravatar($this->aSession['mail'], 32);
                $this->aView['sGravatarSrc64'] = Tools::getGravatar($this->aSession['mail'], 64);
                $this->aView['sGravatarSrc128'] = Tools::getGravatar($this->aSession['mail'], 128);

            }

            // Views common couch
            $this->aView["appLayout"] = '../../../app/Views/layout.tpl'; // @todo degager ca ou constante mais quelquechose
            $this->aView["helpers"] = '../../../app/Views/helpers/';

            // MVC
            $this->aView['sBundle'] = $this->sBundle;
            $this->aView["sController"] = $this->sController;
            $this->aView["sAction"] = $this->sAction;

            // debug
            $this->aView["sEnv"] = ENV;
            $this->aView["aLoadedClass"] = \Library\Core\App::getLoadedClass();
            $this->aView["sDeBugHelper"] = '../../../app/Views/helpers/debug.tpl';

            // Benchmark
            $this->aView["render_time"] = microtime(true);
            $this->aView['framework_started'] = FRAMEWORK_STARTED;
            $this->aView['current_timestamp'] = time();
            $this->aView['rendered_time'] = round($this->aView["render_time"] - FRAMEWORK_STARTED, 3);

            // @see pre dispatch action
            if (method_exists($this, '__preDispatch')) {

                try {
                    $this->__preDispatch();
                } catch (Library\Core\ControllerException $oException) {
                    throw new ControllerException('Pre dispatch action throw an exception: ' . $oException->getMessage(), $oException->getCode());
                    exit();
                }
            }

            // Run mothafucka run!
            $this->{$this->sAction}();

            // @see post dispatch action
            if (method_exists($this, '__postDispatch')) {

                try {
                    $this->__postDispatch();
                } catch (Library\Core\ControllerException $oException) {
                    throw new ControllerException('Post dispatch action throw an exception: ' . $oException->getMessage(), $oException->getCode());
                    exit();
                }
            }
        } else {

            throw new ControllerException(__CLASS__ . ' Error cannot find action ' . $this->sAction);
        }

        return;
    }

    public function render($sTpl, $iStatusXHR = self::XHR_STATUS_OK, $bToString = false, $bLoadAllBundleViews = false)
    {

        if (count($this->aParams) > 0) {
            foreach ($this->aParams as $key => $val) {
                $this->aView[$key] = $val;
            }
        }

        $this->loadLocales($sTpl);

        // check if it's an XMLHTTPREQUEST
        if ($this->isXHR()) {
            // var_dump($this->aView);

            $aResponse = json_encode(array(
                'status' => $iStatusXHR,
                'content' => str_replace(array(
                    "\r",
                    "\r\n",
                    "\n",
                    "\t"
                ), '', \Library\Core\App::initView($sTpl, $this->aView, true, $bLoadAllBundleViews)),
                'debug' => str_replace(array(
                    "\r",
                    "\r\n",
                    "\n",
                    "\t"
                ), '', \Library\Core\App::initView($this->aView["sDeBugHelper"], $this->aView, true, $bLoadAllBundleViews))
            ));
            if ($bToString === true) {
                return $aResponse;
            }

            header('Content-Type: application/json');
            echo $aResponse;
            exit();
        }

        // Render the view using Haanga
        \Library\Core\App::initView($sTpl, $this->aView, $bToString, $bLoadAllBundleViews);
    }

    /**
     * Tell if the request is a XmlHttpRequest
     *
     * @return boolean
     */
    protected function isXHR()
    {
        return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    protected function loadRequest()
    {
        $this->sBundle = Router::getBundle();
        $this->sController = ucfirst(Router::getController()) . 'Controller';
        $this->sAction = Router::getAction() . 'Action';
        ;
        $this->aParams = Router::getParams();
        $this->sLang = Router::getLang();
    }

    /**
     * Build and encode the redirect url if the auth failed
     *
     * @param string $sRedirectUrl
     *            A relative url that start with the '/' root path
     * @return string
     */
    public function buildRedirectUrl($sRedirectBundle = '', $sRedirectController = 'home', $sRedirectAction = 'index')
    {
        // Reset if it's an asynch call or the crud bundle
        if ($this->isXHR()) {

            if ($sRedirectBundle === 'crud') {
                $sRedirectBundle = Router::DEFAULT_BACKEND_BUNDLE;
            }

            $sRedirectController = 'home';
            $sRedirectAction = 'index';
        }
        $sRedirectParam = '/' . $sRedirectBundle . (($sRedirectController !== 'home') ? '/' . $sRedirectController : '') . (($sRedirectAction !== 'index') ? '/' . $sRedirectAction : '');
        $sRedirectUrl = (($this->isXHR()) ? '/error/forbidden/index/redirect/' : '/auth/home/index/redirect/');

        return $sRedirectUrl . urlencode(str_replace('/', '*', $sRedirectParam));
    }

    /**
     * Decode the redirection url
     *
     * @param string $sEncodedRedirectUrl
     *            A relative url that start with the '/' root path
     * @return mixed
     */
    public function decodeRedirectUrl($sEncodedRedirectUrl)
    {
        return str_replace('*', '/', urldecode($sEncodedRedirectUrl));
    }

    /**
     * Simple redirection
     *
     * @param
     *            mixed array|string $mUrl
     * @todo handle router request object totaly abstracted in type and format
     */
    public function redirect($mUrl)
    {
        assert('is_string($mUrl) || is_array($mUrl)');

        if (is_string($mUrl)) {
            header('Location: ' . $mUrl);
            exit();
        } elseif (is_array($mUrl)) {
            if (array_key_exists('request', $mUrl) && isset($mUrl['request']['bundle'], $mUrl['request']['controller'], $mUrl['request']['action'])) {
                $sUrl = '/' . $mUrl['request']['bundle'] . '/' . $mUrl['request']['controller'] . '/' . $mUrl['request']['action'];
            } else {
                throw new RouterException(__METHOD__ . ' malformed redirection request  ');
            }

            header('Location: ' . $sUrl);
        } else {

            throw new RouterException(__METHOD__ . ' wrong request data type (mixed string|array)  ');
        }

        return;
    }

    /**
     * Setup app locales and translations for a given template file
     * @param string $sTpl
     */
    public function loadLocales($sTpl)
    {
        require_once APP_PATH . '/Translations/' . $this->sLang . '/global.php'; // @see globale translation
        if (file_exists(BUNDLES_PATH . $this->sBundle . '/Translations/' . $this->sLang . '/' . $this->sBundle . '.php')) {
            require_once BUNDLES_PATH . $this->sBundle . '/Translations/' . $this->sLang . '/' . $this->sBundle . '.php';
        }
        $this->aView["sAppName"] = $this->aConfig['app']['name'];
        $this->aView["lang"] = $this->sLang;
        $this->aView["tr"] = $tr; // @see loading de la traduction pour la vue
    }

    /**
     * ********************************
     *
     * @todo supprimer
     */
    public function getController()
    {
        return $this->sController;
    }

    public function setController($sController)
    {
        $this->sController = $sController;
    }

    public function getAction()
    {
        return $this->sAction;
    }

    public function setAction($sAction)
    {
        $this->sAction = $sAction;
    }

    public function getCookie()
    {
        return $this->oCookie;
    }

    public function setCookie(\Library\Core\Cookie $oCookie)
    {
        $this->oCookie = $oCookie;
    }

    public function getSession()
    {
        return $this->aSession;
    }

    public function setSession()
    {
        $this->aSession = $_SESSION;
    }

    public function getLang()
    {
        return $this->sLang;
    }

    public function setLang($slang)
    {
        $this->sLang = $slang;
    }
}

class ControllerException extends \Exception
{
}
