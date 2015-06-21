<?php
namespace Library\Core;

/**
 * Execptions management
 *
 * @author Nicolas BONNICI
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
     * @return mixed array|string|null  If an error code was found return error message otherwise null
     */
    public static function getError($iErrorCode)
    {
        return ((isset(self::$aErrors[$iErrorCode])=== true) ? self::$aErrors[$iErrorCode] : null);
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

