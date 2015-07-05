<?php
namespace Library\Core\App\Mvc;

use bundles\auth\Models\AuthModel;
use bundles\user\Entities\User;
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
     * @type bundles\user\Entities\User
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
}

class CoreAuthControllerException extends \Exception
{
}
