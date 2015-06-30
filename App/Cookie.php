<?php
namespace Library\Core\App;

use Library\Core\Pattern\Singleton;

class Cookie
{

    /**
     * Default PHP session key name
     */
    const DEFAULT_PHP_SESSION_KEY = 'PHPSESSID';

    /**
     * Tableau pour stocker les variable de cookie
     *
     * @var array
     */
    private $aCookieVars = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aCookieVars = $_COOKIE;
    }

    /**
     * Instance destructor
     */
    public function destroy()
    {
        $this->aCookieVars = array();
        $this->delete(self::DEFAULT_PHP_SESSION_KEY);
    }

    /**
     * Retrieve cookies values
     *
     * @return array List of set cookies
     */
    public function getVars()
    {
        return $this->aCookieVars;
    }

    /**
     * Retrieve a cookie value
     *
     * @param string $sKeyName
     *            Cookie name
     * @return string Cookie value otherwise NULL
     */
    public function get($sKeyName)
    {
        if (! empty($sKeyName) && array_key_exists($sKeyName, $this->aCookieVars)) {
            return $this->aCookieVars[$sKeyName];
        }
        
        return null;
    }

    /**
     * Set a cookie
     *
     * @param string $sName
     *            Cookie name
     * @param string $sValue
     *            Cookie value
     * @param integer $iLifetime
     *            Cookie lifetime
     * @param string $sPath
     *            Registration path
     * @return boolean TRUE if cookie was successfully set, otherwise FALSE
     */
    public function set($sName, $sValue, $iLifetime = 0, $sPath = '/', $sDomain = COOKIEDOMAINE)
    {
        if (! empty($sName)) {
            return setcookie($sName, $sValue, $iLifetime, $sPath, $sDomain);
        }
        return false;
    }

    /**
     * Delete cookie
     *
     * @param string $sName
     *            Cookie name
     * @param string $sPath
     *            Cookie path
     * @return boolean TRUE if cookie was deleted, otherwise FALSE
     */
    public function delete($sName, $sPath = '/')
    {
        return setcookie($sName, '', time() - 3600, $sPath, COOKIEDOMAINE);
    }
}
