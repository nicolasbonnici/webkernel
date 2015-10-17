<?php
namespace Library\Core\App\Mvc;

use app\Entities\User;
use bundles\auth\Models\AuthModel;
use Library\Core\Acl;
use Library\Core\App\Bundles\Bundle;
use Library\Core\App\Bundles\Bundles;
use Library\Core\App\Cookie;
use Library\Core\App\Mvc\View\View;
use Library\Core\App\Session;
use Library\Core\Bootstrap;
use Library\Core\Router;
use Library\Core\Tools;
use Library\Core\Translation\Translation;

/**
 * Main app controller class
 *
 * @author infradmin
 */
class Controller extends Acl
{

    const CONTROLLER_PRE_DISPATCH_METHOD_NAME   = '__preDispatch';
    const CONTROLLER_POST_DISPATCH_METHOD_NAME  = '__postDispatch';

    /**
     * HTTP status codes
     *
     * @var integer
     */
    const XHR_STATUS_OK = 200;
    const XHR_STATUS_SESSION_EXPIRED = 401;
    const XHR_STATUS_ACCESS_DENIED = 403;
    const XHR_STATUS_NOT_FOUND = 404;
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
    protected $sBundleName;

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
     * Configuration parsed from .ini|.yaml|json
     *
     * @var array
     */
    protected $aConfig;

    /**
     * View instance
     *
     * @var Library\Core\App\Mvc\View\View
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
     * @var \Library\Core\App\Cookie
     */
    protected $oCookie;

    /**
     * Current PHP session
     * @var \Library\Core\App\Session
     */
    protected $oSession;

    /**
     * Currently logged user instance
     *
     * @type \app\Entities\User
     */
    protected $oUser;

    /**
     * Requested bundle instance
     * @var Bundle
     */
    protected $oBundle;

    /**
     * Controller instance constructor
     *
     * @param \app\Entities\User|NULL $oUser
     * @throws ControllerException
     */
    public function __construct($oUser = null)
    {
        # Get current Session instance or create a new one
        $this->startSession();

        # Init Cookie component
        $this->oCookie = new Cookie();

        # Load and parse HTTP request
        $this->loadRequest();

        # Try to find a user even if the Controller doesn't extend Auth
        if (is_null($oUser) === false && $oUser instanceof User && $oUser->isLoaded()) {
            $this->oUser = $oUser;
        } else {
            $this->getLoggedUser();
        }

        # Construct Acl layer parent
        $this->loadAcl($this->oUser);

        # Load translations
        $this->loadTranslations();

        # Load generic parameters to pass to the View component
        $this->loadViewParameters();

        # Load bundle
        $this->loadBundle();

        # Create a new View instance
        // @todo ugly, high cost and weak
        $bLoadAllViewPaths = (($this->sBundleName === 'crud') ? true : false);

        # Build View component to render action
        $aTemplatePaths = array();
        if (is_null($this->oBundle) == false && $this->oBundle->hasTemplate() === true) {
            $aTemplatePaths = array($this->oBundle->getTemplatePath());
        }
        $this->oView = new View($bLoadAllViewPaths, $aTemplatePaths);

        // @see run action & pre|post dispatch callback (optionnal)
        if (method_exists($this, $this->sAction)) {


            $sPreDispatchMethodName = self::CONTROLLER_PRE_DISPATCH_METHOD_NAME;
            $sPostDispatchMethodName = self::CONTROLLER_POST_DISPATCH_METHOD_NAME;
            // @see pre dispatch action hook
            if (method_exists($this, $sPreDispatchMethodName) === true) {

                try {
                    $this->$sPreDispatchMethodName();
                } catch (\Exception $oException) {
                    throw new ControllerException(
                        'Pre dispatch action throw an exception: ' . $oException->getMessage(),
                        $oException->getCode()
                    );
                }

            }

            // Run mothafucka run!
            $this->{$this->sAction}();

            // @see post dispatch action hook
            if (method_exists($this, $sPostDispatchMethodName)) {

                try {
                    $this->$sPostDispatchMethodName();
                } catch (\Exception $oException) {
                    throw new ControllerException(
                        'Post dispatch method throw an exception: ' . $oException->getMessage(),
                        $oException->getCode()
                    );
                }

            }
        } else {
            throw new ControllerException(__CLASS__ . ' Error cannot find action ' . $this->sAction);
        }

        return;
    }

    public function renderView($sView = null)
    {
        if (is_null($sView) === true) {

            $sController = strtolower(str_replace('Controller', '', $this->getController()));
            $sAction = strtolower(str_replace('Action', '', $this->getAction()));

            $sView = $sController . DIRECTORY_SEPARATOR . $sAction . '.tpl';
        }
        $this->oView->render($this->aView, $sView);
    }

    /**
     * Tell if the request is a XmlHttpRequest
     * 
     * @return boolean
     */
    public function isXHR()
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
     * Start PHP sesison handler
     * @return Controller
     */
    public function startSession()
    {
        $this->oSession = Session::getInstance();
        return $this;
    }

    /**
     * Load Http request
     */
    protected function loadRequest()
    {
        $this->sBundleName = Router::getBundle();
        $this->sController = Router::getController() . 'Controller';
        $this->sAction = Router::getAction() . 'Action';
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
                $sRedirectBundle = 'auth';
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
        } elseif (is_array($mUrl)) {
            if (array_key_exists('request', $mUrl) && isset($mUrl['request']['bundle'], $mUrl['request']['controller'], $mUrl['request']['action'])) {
                $sUrl = '/' . $mUrl['request']['bundle'] . '/' . $mUrl['request']['controller'] . '/' . $mUrl['request']['action'];
            } else {
                throw new ControllerException(__METHOD__ . ' malformed redirection request  ');
            }
            header('Location: ' . $sUrl);
        } else {
            throw new ControllerException(__METHOD__ . ' wrong request data type (mixed string|array)  ');
        }

        return;
    }

    /**
     * Setup app locales and translations for a given template file
     * @param string $sTpl
     */
    public function loadTranslations()
    {
        $oTranslation = new Translation($this->sLang, $this->sBundleName);
        $this->aView["lang"] = $this->sLang;
        $this->aView["tr"] = $oTranslation->getTranslations();
    }

    /*
     * Get an array of all Controllers and methods for a given module
     *
     * @param string $sBundle The module name
     * @return array A two dimensional array that contain each controller from a module along with his own methods (actions only)
    */
    public static function build($sBundle)
    {
        $aControllers = array();
        $sControllerPath = Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . '/' . $sBundle . '/Controllers/';
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

    protected function loadAcl($oUser)
    {
        if (is_null($oUser) === false && $oUser instanceof \app\Entities\User && $oUser->isLoaded()) {
            // Check ACL parent component if we have a logged user
            parent::__construct($oUser);
        } else {
            $this->oUser = null;
        }
    }

    /**
     * Load requested bundle
     *
     * @return bool
     */
    protected function loadBundle()
    {
        try {
            $sBundleName = $this->getBundleName();
            if (is_null($sBundleName) === false && empty($sBundleName) === false) {
                $this->oBundle = new Bundle($sBundleName);
            } else {
                $this->oBundle = null;
            }
        } catch (\Exception $oException) {
            $this->oBundle = null;
        }

        return (bool) (is_null($this->oBundle) !== false);
    }

    protected function loadViewParameters()
    {
        $this->aView['aSession'] = $this->oSession->get();
        // @todo provisoire
        if (isset($this->aView['aSession']['auth'])) {
            $aUserSession = $this->aView['aSession']['auth'];
            $this->aView['sGravatarSrc16'] = Tools::getGravatar($aUserSession['mail'], 16);
            $this->aView['sGravatarSrc32'] = Tools::getGravatar($aUserSession['mail'], 32);
            $this->aView['sGravatarSrc64'] = Tools::getGravatar($aUserSession['mail'], 64);
            $this->aView['sGravatarSrc128'] = Tools::getGravatar($aUserSession['mail'], 128);
        }

        // Views common couch
        $this->aView["appLayout"] = '../../../app/Views/layout.tpl'; // @todo degager ca ou constante mais quelquechose
        $this->aView["helpers"] = '../../../app/Views/helpers/';

        // Bootstrap
        $oBundles = new Bundles();
        $this->aView["aAppBundles"] = $oBundles->get();
        $this->aView["sAppName"] = $this->aConfig['app']['name'];
        $this->aView["sAppSupportName"] = $this->aConfig['support']['name'];
        $this->aView["sAppSupportMail"] = $this->aConfig['support']['email'];
        $this->aView["sAppIcon"] = '/lib/bundles/' . $this->sBundleName . '/img/icon.png';

        // MVC infos
        $this->aView['sBundle'] = $this->sBundleName;
        $this->aView["sController"] = $this->sController;
        $this->aView["sControllerName"] = substr($this->sController, 0, strlen($this->sController) - strlen('controller'));
        $this->aView["sAction"] = $this->sAction;
        $this->aView["sActionName"] = substr($this->sAction, 0, strlen($this->sAction) - strlen('action'));

        // debug
        $this->aView["sEnv"] = ENV;
        $this->aView["sDeBugHelper"] = '../../../app/Views/helpers/debug.tpl';
        $this->aView["bIsXhr"] = $this->isXHR();

        // Benchmark
        $this->aView['framework_started'] = FRAMEWORK_STARTED;
        $this->aView['current_timestamp'] = time();
    }

    public function getBundleName()
    {
        return $this->sBundleName;
    }

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

    public function getSession()
    {
        return $this->oSession;
    }

    public function getLang()
    {
        return $this->sLang;
    }

    public function setLang($slang)
    {
        $this->sLang = $slang;
    }

    /**
     * Retrieve loggued User instance
     * @return User|null
     */
    protected function getLoggedUser()
    {
        if ($this->oUser instanceof User && $this->oUser->isLoaded() === true) {
            return $this->oUser;
        } elseif ($this->loadBySession() === true) {
            return $this->oUser;
        } else {
            return null;
        }
    }

    /**
     * Method to retrieve User on a non Auth Controller
     */
    protected function loadBySession()
    {
        $this->oUser = new User();
        try {
            $aSession = $this->oSession->get();
            if (isset($aSession[Auth::SESSION_AUTH_KEY]) === true) {
                $this->oUser->loadByParameters(array(
                    'iduser' => $aSession[Auth::SESSION_AUTH_KEY]['iduser'],
                    'mail' => $aSession[Auth::SESSION_AUTH_KEY]['mail'],
                    'token' => $aSession[Auth::SESSION_AUTH_KEY]['token'],
                    'confirmed' => AuthModel::USER_ACTIVATED_STATUS,
                    'created' => $aSession[Auth::SESSION_AUTH_KEY]['created']
                ));

                if ($this->oUser->isLoaded()) {

                    $aUserAuth = array();
                    foreach ($this->oUser as $key => $mValue) {
                        $aUserAuth[$key] = $mValue;
                    }
                    // Regenerate session token
                    $aUserAuth['token'] = $this->generateToken();

                    // Unset password
                    unset($aUserAuth['pass']);

                    $this->oSession->add('auth', $aUserAuth);

                    $this->oUser->token = $aUserAuth['token'];
                    return $this->oUser->update();
                }
            }
            return false;
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Generate session token for User
     *
     * @return string
     */
    private function generateToken()
    {
        return hash('SHA256', uniqid((double) microtime() * 1000000, true));
    }
}

class ControllerException extends \Exception
{
}
