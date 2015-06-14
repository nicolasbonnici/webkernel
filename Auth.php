<?php
namespace Core;

use bundles\user\Entities\User;
use Core\App\Session;

class CoreAuthControllerException extends \Exception
{
}

/**
 * Simple auth controller layer
 * Just create a Controller that extend this class to restrict access to logged users
 *
 * @author Nicolas Bonnici <nicolasbonnici@gmail.com>
 */
class Auth extends Controller
{

    /**
     * Currently logged user instance
     *
     * @type bundles\user\Entities\User
     */
    protected $oUser;

    /**
     * Session instance to store current PHP session
     * @var array
     */
    protected $oSession = array();


    public function __construct()
    {

        /**
         * Check php session
         */
        if (
            is_array($this->oSession->getSession('token')) === false &&
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
            $this->oUser->loadByParameters(array(
                'iduser' => $this->oSession->getSession('iduser'),
                'mail' => $this->oSession->getSession('mail'),
                'token' => $this->oSession->getSession('token'),
                'created' => $this->oSession->getSession('created'),
            ));

            if ($this->oUser->isLoaded()) {

                foreach ($this->oUser as $key => $mValue) {
                    $this->oSession->addSession($key, $mValue);
                }

                // Regenerate session token
                $this->oSession->addSession('token', $this->generateToken());

                // Unset password
                $this->oSession->deleteSession('pass');

                $this->oUser->token = $this->oSession->getSession('token');

                return $this->oUser->update();
            }
        } catch (CoreEntityException $oException) {
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
