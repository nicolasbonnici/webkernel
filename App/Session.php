<?php
namespace Library\Core\App;
use Library\Core\Pattern\Singleton;

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
    protected static $aSession = array();

    protected function __construct()
    {
        parent::__construct();

        self::set($_SESSION);
    }

    public static function getInstance()
    {
        self::set($_SESSION);
        return parent::getInstance();
    }

    /**
     * Session accessor
     *
     * @param string $sSessionKey   string OR NULL to get whole session as array
     * @return array
     */
    public static function get($sSessionKey = null)
    {
        return (
        (isset(self::$aSession[$sSessionKey]))
            ? self::$aSession[$sSessionKey]
            : self::$aSession
        );
    }

    /**
     * Add a session variable
     *
     * @param string $sKey
     * @param mixed string|int|array|object $mValue
     * @return $this
     */
    public static function add($sKey, $mValue)
    {
        $_SESSION[$sKey] = $mValue;
        self::$aSession[$sKey] = $mValue;
        return isset($_SESSION[$sKey], self::$aSession[$sKey]);
    }

    /**
     * Add session variables from an array
     * @param array $aSession
     * @return Session
     */
    public static function set(array $aSession)
    {
        $_SESSION = array_merge($_SESSION, $aSession);
        self::$aSession = array_merge(self::$aSession, $aSession);
        return true;
    }

    /**
     * Delete session key
     *
     * @param string $sSessionKey
     * @return $this
     */
    public static function delete($sSessionKey)
    {
        unset($_SESSION[$sSessionKey]);
        unset(self::$aSession[$sSessionKey]);

        return (isset($_SESSION[$sSessionKey], self::$aSession[$sSessionKey]) === false);
    }

    /**
     * Destroy the current session
     */
    public static function destroy()
    {
        self::$aSession = array();
        $_SESSION = array();
        session_destroy();
    }

}