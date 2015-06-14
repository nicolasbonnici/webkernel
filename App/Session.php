<?php
namespace Core\App;
use Core\Pattern\Singleton;

/**
 * Session managment compoonent
 *
 * @package Core\App
 */

class Session extends Singleton {

    /**
     * Current PHP session
     * @var array
     */
    protected $aSession = array();

    /**
     * Session accessor
     *
     * @param string $sSessionKey   string OR NULL to get whole session as array
     * @return array
     */
    public function getSession($sSessionKey = null)
    {
        return (
        (isset($this->aSession[$sSessionKey]))
            ? $this->aSession[$sSessionKey]
            : $this->aSession
        );
    }

    /**
     * Add a session variable
     *
     * @param string $sKey
     * @param mixed string|int|array|object $mValue
     * @return $this
     */
    public function addSession($sKey, $mValue)
    {
        $_SESSION[$sKey] = $mValue;
        $this->aSession[$sKey] = $mValue;
        return $this;
    }

    /**
     * Delete session key
     *
     * @param string $sSessionKey
     * @return $this
     */
    public function deleteSession($sSessionKey)
    {
        unset($this->aSession[$sSessionKey]);
        unset($_SESSION[$sSessionKey]);
        return $this;
    }

    /**
     * Add session variables from an array
     * @param array $aSession
     * @return Session
     */
    public function setSession(array $aSession)
    {
        $this->aSession = array_merge($this->aSession, $aSession);
        return $this;
    }

}