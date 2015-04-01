<?php
namespace Library\Core;

use bundles\user\Entities\User;

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

    public function __construct()
    {
        $this->loadRequest();

        /**
         * Check php session
         */
        if (isset($_SESSION['token']) && ($this->_session = $_SESSION) && $this->checkSessionintegrity()) {
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
                'iduser' => $this->_session['iduser'],
                'mail' => $this->_session['mail'],
                'token' => $this->_session['token'],
                'created' => $this->_session['created']
            ));

            if ($this->oUser->isLoaded()) {

                foreach ($this->oUser as $key => $mValue) {
                    $_SESSION[$key] = $mValue;
                }

                // Regenerate session token
                $_SESSION['token'] = $this->generateToken();
                // Unset password
                unset($_SESSION['pass']);

                $this->oUser->token = $_SESSION['token'];

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

class CoreAuthControllerException extends \Exception
{
}
