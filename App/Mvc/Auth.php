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

    public function __construct()
    {
        $this->loadRequest();
        $this->startSession();

        // If a session was found we check the integrity
        $aSession = $this->oSession->get();
        if (
        	isset($aSession[Auth::SESSION_AUTH_KEY]) === true &&
        	$this->loadUserBySession() === true &&
            $this->oUser->isLoaded() === true
		) {
            parent::__construct($this->oUser);
        } else {
            $this->redirect($this->buildRedirectUrl(Router::getBundle(), Router::getController(), Router::getAction()));
        }
    }

    /**
     * Generic action to each bundle for storing or updating a configuration variable
     */
    public function configureAction($iXhrStatus = Controller::XHR_STATUS_ERROR)
    {
        $sMessage = 'Error occur...';
        if (isset($this->aParams['name'], $this->aParams['value']) === true) {
            $oConfiguration = new Configuration($this->getBundleName());
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
