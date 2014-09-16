<?php
namespace Library\Core;

/**
 * HTTP header management class
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 */
class Http
{
    const HEADER_CONTENT_TYPE_JSON = 'application/json';

    /**
     * HTTP status code
     * @var integer
     */
    protected static $iStatus = 200;

    /**
     * Response header
     * @var array
     */
    protected static $aHeaders = array();

    /**
     * Set HTTP status code
     * @param integer $iStatus Status code
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    public static function setStatus($iStatus)
    {
        assert('\\core\\Validator::integer($iStatus, 100) === \\core\\Validator::STATUS_OK');
        self::$iStatus = $iStatus;
    }

    /**
     * Retrieve value of a specific header
     * If none is given, retrieves all headers
     * @param string $sName Header name
     * @return array|string|null Header value if set, otherwise NULL / List of headers
     */
    public static function getHeader($sName = null)
    {
        if (is_null($sName)) {
            return self::$aHeaders;
        }

        return isset(self::$aHeaders[$sName]) ? self::$aHeaders[$sName] : null;
    }

    /**
     * Set specific header
     * @param string $sName Header name
     * @param string $sValue Header value
     */
    public static function setHeader($sName, $sValue)
    {
        self::$aHeaders[$sName] = $sValue;
    }

    /**
     * Set several headers at once
     * @param array $aHeaders List of headers
     * @param boolean $bReplace Replace all previous headers by given ones
     */
    public static function setHeaders(array $aHeaders, $bReplace = true)
    {
        if ($bReplace) {
            self::$aHeaders = $aHeaders;
        } else {
            self::$aHeaders = array_merge(self::$aHeaders, $aHeaders);
        }
    }

    /**
     * Set content type header
     * @param string $sType Content type
     * @todo declarer en static les type tolérés
     */
    public static function setContentType($sType)
    {
        self::$aHeaders['Content-Type'] = $sType;
    }

    /**
     * Send headers with HTTP status code
     */
    public static function sendHeaders()
    {
        http_response_code(self::$iStatus);
        foreach (self::$aHeaders as $sName => $sValue) {
            header($sName . ': ' . $sValue);
        }
    }
}

class HttpException extends \Exception
{
}
