<?php
namespace Library\Core\App\Mvc;

use app\Entities\User;
use bundles\auth\Models\AuthModel;
use Library\Core\Acl;
use Library\Core\App\Bundles\Bundle;
use Library\Core\App\Bundles\Bundles;
use Library\Core\App\Cookie;
use Library\Core\App\Hooks\Hook;
use Library\Core\App\Mvc\View\View;
use Library\Core\App\Session;
use Library\Core\App\Widgets\WidgetAbstract;
use Library\Core\Bootstrap;
use Library\Core\Http\Headers;
use Library\Core\Router\Router;
use Library\Core\Traits\Gravatar;
use Library\Core\Translation\Translation;

/**
 * Main app controller class
 *
 * @author infradmin
 */
class Controller
{

    /**
     * Default folder name where the Controllers are stored under the project or the bundles
     */
    const CONTROLLER_FOLDER_NAME = 'Controllers';

    /**
     * Controller file name pattern (ex: FooController)
     *
     * @var string
     */
    const CONTROLLER_FILE_PATTERN = 'Controller';

    /**
     * Action method name pattern to declare a controller action
     * @var string
     */
    const CONTROLLER_ACTION_PATTERN = 'Action';

    /**
     * Controllers pre and post dispatch routines methods names
     */
    const CONTROLLER_PRE_DISPATCH_METHOD_NAME   = '__preDispatch';

    /**
     * HTTP status codes
     *
     * @var integer
     */
    const XHR_STATUS_OK                 = 200;
    const XHR_STATUS_SESSION_EXPIRED    = 401;
    const XHR_STATUS_ACCESS_DENIED      = 403;
    const XHR_STATUS_NOT_FOUND          = 404;
    const XHR_STATUS_ERROR              = 500;

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
     * Hook instance
     * @var Hook
     */
    protected $oHook;

    /**
     * Configuration parsed from .ini|.yaml|json
     *
     * @var array
     */
    protected $aConfig;

    /**
     * View instance
     *
     * @var View
     */
    protected $oView;

    /**
     * Parameters to pass to the current View instance
     *
     * @var array
     */
    protected $aView;

    /**
     * Cookie instance
     *
     * @var Cookie
     */
    protected $oCookie;

    /**
     * Current PHP session
     *
     * @var Session
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
     *
     * @var Bundle
     */
    protected $oBundle;

    /**
     * Controller instance constructor
     *
     * @throws ControllerException
     */
    public function __construct()
    {
        # Get current Session instance or create a new one
        $this->startSession();

        # Init Cookie component
        $this->oCookie = new Cookie();

        # Load and parse HTTP request
        $this->loadRequest();

        # Detect if a user is logged
        $this->getLoggedUser();

        # Load translations
        $this->loadTranslations();

        # Load generic parameters to pass to the View component
        $this->loadDefaultViewParameters();

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

        // @see run action & pre dispatch callback (optional)
        if (method_exists($this, $this->sAction)) {

            $sPreDispatchMethodName = self::CONTROLLER_PRE_DISPATCH_METHOD_NAME;
            if (method_exists($this, $sPreDispatchMethodName) === true) {

                try {
                    # Run pre dispatch hook method if available
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

        } else {
            /**
             * @todo handle properly the 404 error and root if available to the error bundle
             */
            $oHttpHeader = new Headers();
            $oHttpHeader->setStatus(404);
            $oHttpHeader->sendHeaders();
            exit;
        }

        return;
    }

    /**
     * Render a View from a Controller action method
     *
     * @param string $sView         The relative path to the template file
     */
    public function renderView($sView = null)
    {
        if (is_null($sView) === true) {

            $sController = strtolower(str_replace(self::CONTROLLER_FILE_PATTERN, '', $this->getController()));
            $sAction = strtolower(str_replace(self::CONTROLLER_ACTION_PATTERN, '', $this->getAction()));

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
     * Start PHP sesison handler
     *
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
        $this->sController = Router::getController() . self::CONTROLLER_FILE_PATTERN;
        $this->sAction = Router::getAction() . self::CONTROLLER_ACTION_PATTERN;
        $this->aParams = Router::getParams();
        $this->sLang = Bootstrap::getLocale();
    }

    /**
     * Build and encode the redirect url if the auth failed
     *
     * @param string $sRedirectUrl  A relative url that start with the '/' root path
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
        $sRedirectUrl = (($this->isXHR()) ? '/error/forbidden/index/redirect/' : '/login/');

        return $sRedirectUrl . urlencode(str_replace('/', '*', $sRedirectParam));
    }

    /**
     * Decode the redirection url
     *
     * @param string $sEncodedRedirectUrl   A relative url that start with the '/' root path
     * @return string
     */
    public function decodeRedirectUrl($sEncodedRedirectUrl)
    {
        return str_replace('*', '/', urldecode($sEncodedRedirectUrl));
    }

    /**
     * Simple HTTP redirection handler
     *
     * @param mixed array|string $mUrl
     * @todo handle router request object totaly abstracted in type and format and move to the HTTP component
     * @todo implémenter Router::redirect() en lieu et place de cette méthode
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
        $sControllerPath = Bootstrap::getPath(Bootstrap::PATH_BUNDLES) . '/' . $sBundle . '/'. self::CONTROLLER_FOLDER_NAME .'/';
        $aFiles = array_diff(scandir($sControllerPath), array(
            '..',
            '.'
        ));

        foreach ($aFiles as $sController) {
            if (preg_match('#' . self::CONTROLLER_FILE_PATTERN . '.php$#', $sController)) {
                $aControllers[substr($sController, 0, strlen($sController) - strlen(self::CONTROLLER_FILE_PATTERN . '.php'))] = self::buildActions($sBundle, $sController);
            }
        }

        return $aControllers;
    }


    /**
     * Get all actions from a given module and controller (this method only return [foo]Action() methods)
     *
     * @param string $sBundle The module name
     * @param string $sController The controller name to parse
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
     * Load requested bundle
     *
     * @return bool
     */
    protected function loadBundle()
    {
        try {
            $sBundleName = $this->getBundleName();
            if (is_null($sBundleName) === false && empty($sBundleName) === false) {
                $this->oBundle = new Bundle($sBundleName, $this->oUser);
            } else {
                $this->oBundle = null;
            }
        } catch (\Exception $oException) {
            $this->oBundle = null;
        }

        return (bool) (is_null($this->oBundle) !== false);
    }

    /**
     * Load common parameters to each rendered controller actions
     *
     * @todo ligthweight a little
     */
    protected function loadDefaultViewParameters()
    {
        # Load Hook component
        $this->oHook = new Hook();
        $this->aView['aHooks'] = array();

        $this->aView['aSession'] = $this->oSession->get();
        // @todo provisoire
        if (isset($this->aView['aSession']['auth'])) {
            $aUserSession = $this->aView['aSession']['auth'];
            $this->aView['sGravatarSrc16'] = Gravatar::getGravatar($aUserSession['mail'], 16);
            $this->aView['sGravatarSrc32'] = Gravatar::getGravatar($aUserSession['mail'], 32);
            $this->aView['sGravatarSrc64'] = Gravatar::getGravatar($aUserSession['mail'], 64);
            $this->aView['sGravatarSrc128'] = Gravatar::getGravatar($aUserSession['mail'], 128);
        }

        // Views common couch
        $this->aView["appLayout"] = '../../../app/Views/layout.tpl'; // @todo degager ca ou computer
        $this->aView["helpers"] = '../../../app/Views/helpers/';

        // Bootstrap
        $oBundles = new Bundles();
        $this->aView["aAppBundles"] = $oBundles->get();
        $this->aView["sAppName"] = $this->aConfig['app']['name'];
        $this->aView["sAppSupportName"] = $this->aConfig['support']['name'];
        $this->aView["sAppSupportMail"] = $this->aConfig['support']['email'];
        $this->aView["sAppIcon"] = '/lib/bundles/' . $this->sBundleName . '/img/icon.png';

        // MVC infos
        $this->aView['sLocale'] = $this->sLang;
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

    /**
     * Method to retrieve User on a non Auth Controller
     */
    protected function loadUserBySession()
    {

        try {
            $this->oUser = new User();
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
                    // Unset user's password
                    unset($this->oUser->pass);
                    return true;
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

    /**
     * Register a Widget to render in the View
     *
     * @param string $sHookName
     * @param WidgetAbstract $oWidget
     */
    public function registerViewWidget($sHookName, WidgetAbstract $oWidget)
    {
        # Store in Hook instance
        $this->oHook->registerWidget($sHookName, $oWidget);
        # Refresh view parameters
        $this->aView['aHooks'] = $this->oHook->get();
    }

    /**
     * Get requested bundle name
     *
     * @return string
     */
    public function getBundleName()
    {
        return $this->sBundleName;
    }

    /**
     * Get requested Controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->sController;
    }

    /**
     * Get requested action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->sAction;
    }

    /**
     * Get Cookie instance
     *
     * @return Cookie
     */
    public function getCookie()
    {
        return $this->oCookie;
    }

    /**
     * Get Session instance
     *
     * @return Session
     */
    public function getSession()
    {
        return $this->oSession;
    }

    /**
     * Retrieve logged User instance
     *
     * @return User
     */
    protected function getLoggedUser()
    {
        if (isset($this->oUser) === true && $this->oUser->isLoaded() === true) {
            return $this->oUser;
        } elseif ($this->loadUserBySession() === true) {
            return $this->oUser;
        } else {
            return null;
        }
    }

}

class ControllerException extends \Exception
{
}
