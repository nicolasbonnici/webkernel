<?php
namespace Library\Core\App\Mvc;

use bundles\auth\Models\AuthModel;
use app\Entities\User;
use Library\Core\App\Configuration;
use Library\Core\App\Mvc\View\View;
use Library\Core\Router;

/**
 * Simple auth controller layer
 * Just create a Controller that extend this class to restrict access to logged users
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Auth extends Controller
{

    const SESSION_AUTH_KEY = 'auth';

    /**
     * Currently logged user instance
     *
     * @type \app\Entities\User
     */
    protected $oUser;

    public function __construct()
    {
        $this->loadRequest();
        $this->startSession();

        // Try to retrieve session token
        $aSession = $this->oSession->get();
        if (
        	isset($aSession['auth']) === true &&
        	$this->checkSessionintegrity()
		) {
            parent::__construct($this->oUser, $this->oBundleConfig);
        } else {
            $this->redirect($this->buildRedirectUrl(Router::getBundle(), Router::getController(), Router::getAction()));
        }
    }

    /**
     * Validate session integrity
     *
     * @return bool
     */
    protected function checkSessionintegrity()
    {
        $this->oUser = new User();
        try {
            $aSession = $this->oSession->get('auth');
            $this->oUser->loadByParameters(array(
                'iduser' => $aSession['iduser'],
                'mail' => $aSession['mail'],
                'token' => $aSession['token'],
                'confirmed' => AuthModel::USER_ACTIVATED_STATUS,
                'created' => $aSession['created']
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
        } catch (\Exception $oException) {
            return false;
        }
    }

    /**
     * Generate session token
     *
     * @return string
     */
    private function generateToken()
    {
        return hash('SHA256', uniqid((double) microtime() * 1000000, true));
    }

    /**
     * Generic action to each bundle for storing or updating a configuration variable
     */
    public function configureAction($iXhrStatus = Controller::XHR_STATUS_ERROR)
    {
        $sMessage = 'Error occur...';
        if (isset($this->aParams['name'], $this->aParams['value']) === true) {
            $oConfiguration = new Configuration($this->getBundle());
            $bConfAdded = $oConfiguration->set(
                $this->aParams['name'],
                $this->aParams['value']
            );
            if ($bConfAdded === true) {
                $iXhrStatus = Controller::XHR_STATUS_OK;
                $sMessage = 'Success';
            }
        }

        $this->aView['sMessage'] = $sMessage;
        $this->oView->render($this->aView, View::BLANK_LAYOUT, $iXhrStatus);
    }

}

class CoreAuthControllerException extends \Exception
{
}
