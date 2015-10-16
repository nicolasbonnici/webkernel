<?php
namespace Library\Core\App;

/**
 * This class handle cookie manipulation
 *
 * Class Cookie
 * @package Library\Core\App
 */
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
    protected $aCookieVars = array();

    /**
     * Cookie domain
     * @var string
     */
    protected $sCookieDomain;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->aCookieVars = $_COOKIE;
        $this->setCookieDomain($_SERVER['SERVER_NAME']);
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
    public function set($sName, $sValue, $iLifetime = 0, $sPath = '/')
    {
        if (! empty($sName)) {
            return setcookie($sName, $sValue, $iLifetime, $sPath, $this->sCookieDomain);
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
        return setcookie($sName, '', time() - 3600, $sPath, $this->sCookieDomain);
    }

    /**
     * Set the current domain for the Cookie instance
     *
     * @param string $sDomain
     * @return Cookie
     */
    public function setCookieDomain($sDomain)
    {
        $this->sCookieDomain = $sDomain;
        return $this;
    }

    /**
     * Domain for cookie accessor
     * @return string
     */
    public function getCookieDomain()
    {
        return $this->sCookieDomain;
    }
}
