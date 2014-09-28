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
     * XHR Errors codes
     *
     * @var integer
     */
    const XHR_STATUS_OK = 200;
    const XHR_STATUS_SESSION_EXPIRED = 401;
    const XHR_STATUS_ACCESS_DENIED = 403;
    const XHR_STATUS_ERROR = 500;

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
     * Current request sent parameters
     * @var array
     */
    protected $aParams = array();

    /**
     * Configuration parsed from .ini|.yaml
     *
     * @var array
     */
    protected $aConfig;

    /**
     * View instance
     *
     * @var \Library\Core\View
     */
    protected $oView;

    /**
     * Parameters to pass to the current View instance
     *
     * @var array
     */
    protected $aView;

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
     * Controller instance constructor
     *
     * @param \bundles\user\Entities\User|NULL $oUser
     * @throws ControllerException
     */
    public function __construct($oUser = NULL)
    {
        $this->aConfig = \Library\Core\App::getConfig();
        $this->setSession();
        $this->loadRequest();

        // Load a view instance
        $bLoadAllViewPaths = (($this->sBundle === 'crud') ? true : false);
        $this->oView = new View($bLoadAllViewPaths);

        if (! is_null($oUser) && $oUser instanceof \bundles\user\Entities\User && $oUser->isLoaded()) {
            $this->oUser = $oUser;
            // Check ACL parent component if we have a logged user
            parent::__construct($oUser);
        } else {
            $this->oUser = null;
        }

        // @see run action & pre|post dispatch callback (optionnal)
        if (method_exists($this, $this->sAction)) {
            $this->loadLocales();
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

            // App
            $this->aView["aAppBundles"] = App::getBundles();
            $this->aView["sAppName"] = $this->aConfig['app']['name'];
            $this->aView["sAppSupportName"] = $this->aConfig['support']['name'];
            $this->aView["sAppSupportMail"] = $this->aConfig['support']['email'];
            $this->aView["sAppIcon"] = '/lib/bundles/' . $this->sBundle . '/img/icon.png';

            // MVC infos
            $this->aView['sBundle'] = $this->sBundle;
            $this->aView["sController"] = $this->sController;
            $this->aView["sControllerName"] = substr($this->sController, 0, strlen($this->sController) - strlen('controller'));
            $this->aView["sAction"] = $this->sAction;
            $this->aView["sActionName"] = substr($this->sAction, 0, strlen($this->sAction) - strlen('action'));

            // debug
            $this->aView["sEnv"] = ENV;
            $this->aView["aLoadedClass"] = \Library\Core\App::getLoadedClass();
            $this->aView["sDeBugHelper"] = '../../../app/Views/helpers/debug.tpl';
            $this->aView["bIsXhr"] = $this->isXHR();

            // Benchmark
            $this->aView["render_time"] = microtime(true);
            $this->aView['framework_started'] = FRAMEWORK_STARTED;
            $this->aView['current_timestamp'] = time();
            $this->aView['rendered_time'] = round($this->aView["render_time"] - FRAMEWORK_STARTED, 3);

            // @see pre dispatch action hook
            if (method_exists($this, '__preDispatch')) {

                try {
                    $this->__preDispatch();

                    // Load assets dependancies for client components (can be overide under the __preDispatch() method)
                    $this->aView['sComponentsDependancies'] = $this->oView->buildClientComponents();

                } catch (Library\Core\ControllerException $oException) {
                    throw new ControllerException('Pre dispatch action throw an exception: ' . $oException->getMessage(), $oException->getCode());
                    exit();
                }
            }


            // Run mothafucka run!
            $this->{$this->sAction}();

            // @see post dispatch action hook
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

    /**
     * Tell if the request is a XmlHttpRequest
     *
     * @return boolean
     */
    public static function isXHR()
    {
        return (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    /**
     * Tell if we have a valid logged in user instance
     * @return boolean
     */
    protected function isValidUserLogged()
    {
        return (isset($this->oUser) && $this->oUser->isLoaded() && $this->oUser->getId() === intval($_SESSION['iduser']));
    }

    /**
     * Load Http request
     */
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
     * @todo handle router request object totaly abstracted in type and format and move to the HTTP component
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
    public function loadLocales()
    {
        require_once APP_PATH . '/Translations/' . $this->sLang . '/global.php'; // @see globale translation
        if (file_exists(BUNDLES_PATH . $this->sBundle . '/Translations/' . $this->sLang . '.php')) {
            require_once BUNDLES_PATH . $this->sBundle . '/Translations/' . $this->sLang . '.php';
        }
        $this->aView["lang"] = $this->sLang;
        $this->aView["tr"] = $tr; // @see loading de la traduction pour la vue
    }

    /*
     * Get an array of all Controllers and methods for a given module
     *
     * @param string $sBundle The module name
     * @return array A two dimensional array that contain each controller from a module along with his own methods (actions only)
    */
    public static function build($sBundle)
    {
        assert('!empty($sBundle) && is_string($sBundle) && is_dir(BUNDLES_PATH . "/" . $sBundle . "/Controllers/")');
        $aControllers = array();
        $sControllerPath = BUNDLES_PATH . '/' . $sBundle . '/Controllers/';
        $aFiles = array_diff(scandir($sControllerPath), array(
            '..',
            '.'
        ));

        foreach ($aFiles as $sController) {
            if (preg_match('#Controller.php$#', $sController)) {
                $aControllers[substr($sController, 0, strlen($sController) - strlen('Controller.php'))] = self::buildActions($sBundle, $sController);
            }
        }

        return $aControllers;
    }


    /**
     * Get all actions from a given module and controller (this method only return [foo]Action() methods)
     *
     * @param string $sBundle
     *            The module name
     * @param string $sController
     *            The controller name to parse
     * @return array A two dimensional array with the controllers and their methods (actions only)
     */
    public static function buildActions($sBundle, $sController)
    {
        assert('!empty($sController) && is_string($sController) && !empty($sBundle) && is_string($sBundle)');
        $aActions = array();
        $aMethods = get_class_methods('\bundles\\' . $sBundle . '\Controllers\\' . substr($sController, 0, strlen($sController) - strlen('.php')));
        if (count($aMethods) > 0) {
            foreach ($aMethods as $sMethod) {
                if (preg_match('#Action$#', $sMethod) && $sMethod !== 'getAction' && $sMethod !== 'setAction') {
                    $aActions[] = substr($sMethod, 0, strlen($sMethod) - strlen('Action'));
                }
            }
        }

        return $aActions;
    }

    /**
     * ********************************
     *
     * @todo grep et supprimer
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
