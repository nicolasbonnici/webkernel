<?php
namespace Library\Core\Exception;

/**
 * This inherit this component all framework components exceptions
 *
 * Class CoreException
 * @package Library\Core\Exception
 */
class CoreException extends \Exception
{

    /**
     * Two dimensional array to store iErrorCode => sErrorMessage
     * @var array
     */
    public static $aErrors = array();

    /**
     * Errors codes and messages accessor
     *
     * @param integer $iErrorCode
     * @return string If an error code was found return error message otherwise null
     */
    public static function getError($iErrorCode)
    {
        return ((isset(self::$aErrors[$iErrorCode]) === true) ? self::$aErrors[$iErrorCode] : 'not found');
    }

    /**
     * Get errors codes and messages
     *
     * @return array
     */
    public static function getErrors()
    {
        return self::$aErrors;
    }

}

