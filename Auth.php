<?php

namespace Library\Core;

/**
 * Simple auth layer
 * Just create a Controller that extend this class to restrict access to loggued users
 *
 * @author infradmin
 */
class Auth extends Controller {

    /**
     * Currently logged user instance
     *
     * @type \app\Entities\User
     */
    protected $oUser;

    public function __construct() {

        $this->loadRequest();

        /**
         * Check php session
         */
        if (
            isset($_SESSION['token']) &&
            ($this->_session = $_SESSION) &&
            $this->checkSessionintegrity()
        ) {
            parent::__construct($this->oUser);
        } else {
            $this->redirect($this->encodeRedirectUrlParam( Router::getBundle() ));
        }

    }

    /**
     * Validate session integrity
     * @return bool
     */
    public function checkSessionintegrity() {
        $this->oUser = new \app\Entities\User();

        try {
            $this->oUser->loadByParameters(
                array(
                    'iduser' => $this->_session['iduser'],
                    'mail' => $this->_session['mail'],
                    'token' => $this->_session['token'],
                    'created' => $this->_session['created']
                )
            );

            if ($this->oUser->isLoaded()) {

                foreach ($this->oUser as $key=>$mValue) {
                    $_SESSION[$key] = $mValue;
                }

                // Regenerate session token
                $_SESSION['token'] = $this->generateToken();
                // Unset password
                unset($_SESSION['pass']);

                $this->oUser->token = $_SESSION['token'];

                return $this->oUser->update();
            }
        } catch(CoreEntityException $oException) {
            return false;
        }
    }

    /**
     * Generate token session
     *
     * @return int
     */
    private function generateToken() {
        return hash('SHA256', uniqid((double)microtime()*1000000, true));
    }

}

class CoreAuthControllerException extends \Exception {}

?>
