<?php
namespace Library\Core;

/**
 * Data validator
 *
 * @author Antoine <antoine.preveaux@bazarchic.com>
 * @author niko <nicolasbonnici@gmail.com>
 */

class Validator
{
    /**
     * Constantes de validation
     */
    const STATUS_OK               = 1; // @todo mettre 0 sinon si on oublie de tester la valeur de retour dans le if, on passera toujours dedans if (\core\Validator::integer('A')) => on passe
    const STATUS_INVALID          = 2;
    const STATUS_OUT_OF_RANGE     = 3;
    const STATUS_EMPTY_MANDATORY  = 4;
    const STATUS_ALREADY_EXISTS   = 5;

    /******************
     * PHP validators *
     ******************/
    /**
     * Check that given argument is an integer
     * @param integer $iInteger Potential integer to check
     * @param integer $iMinValue Minimum value of integer (optional)
     * @param integer $iMaxValue Maximum value of integer (optional)
     * @return integer Check status
     */
    static public function integer(&$iInteger, $iMinValue = null, $iMaxValue = null)
    {
        assert('(is_null($iMinValue) || is_int($iMinValue)) && (is_null($iMaxValue) || is_int($iMaxValue))');

        if (!is_numeric($iInteger) || $iInteger != (int) $iInteger) {
            return self::STATUS_INVALID;
        }

        if (!is_null($iMinValue) && $iInteger < $iMinValue) {
            return self::STATUS_OUT_OF_RANGE;
        }

        if (!is_null($iMaxValue) && $iInteger > $iMaxValue) {
            return self::STATUS_OUT_OF_RANGE;
        }

        $iInteger = (int) $iInteger;

        return self::STATUS_OK;
    }

    /**
     * Check that given argument is a float
     * @param float $fFloat Potential float to check
     * @param float $fMinValue Minimum value of float (optional)
     * @param float $fMaxValue Maximum value of float (optional)
     * @return integer Check status
     */
    static public function float(&$fFloat, $fMinValue = null, $fMaxValue = null)
    {
        assert('(is_null($fMinValue) || is_float($fMinValue)) && (is_null($fMaxValue) || is_float($fMaxValue))');

        if (!is_numeric($fFloat) || $fFloat != (float) $fFloat) {
            return self::STATUS_INVALID;
        }

        if (!is_null($fMinValue) && $fFloat < $fMinValue) {
            return self::STATUS_OUT_OF_RANGE;
        }

        if (!is_null($fMaxValue) && $fFloat > $fMaxValue) {
            return self::STATUS_OUT_OF_RANGE;
        }

        $fFloat = (float) $fFloat;

        return self::STATUS_OK;
    }

    /**
     * Check that given argument is a string
     * @param string $sString Potential string to check
     * @param string $iMinLength Minimum length of string (optional)
     * @param string $iMaxLength Maximum length of string (optional)
     * @return integer Check status
     */
    static public function string(&$sString, $iMinLength = null, $iMaxLength = null)
    {
        assert('(is_null($iMinLength) || is_int($iMinLength)) && (is_null($iMaxLength) || is_int($iMaxLength))');

        if (!is_string($sString)) {
            return self::STATUS_INVALID;
        }

        if (!is_null($iMinLength) && strlen($sString) < $iMinLength) {
            return self::STATUS_OUT_OF_RANGE;
        }

        if (!is_null($iMaxLength) && strlen($sString) > $iMaxLength) {
            return self::STATUS_OUT_OF_RANGE;
        }

        return self::STATUS_OK;
    }

    /**
     * Check that given argument is a boolean
     * @param boolean $bBoolean Potential boolean to check
     * @return integer Check status
     */
    static public function boolean(&$bBoolean)
    {
        if (!in_array($bBoolean, array(true, false, 0, 1))) {
            return self::STATUS_INVALID;
        }

        $bBoolean = (bool) $bBoolean;

        return self::STATUS_OK;
    }

    /**
     * Check and cast a timestamp value
     * @param integer|mixed $iTimestamp
     * @return integer Check status
     */
    static public function timestamp(&$iTimestamp)
    {
        return ((($iTimestamp = intval($iTimestamp)) && $iTimestamp > 0) ? self::STATUS_OK : self::STATUS_INVALID);
    }

    /**
     * Check that given argument is a valid MD5 hash
     * @param string $sMD5 String to check
     * @return integer Check status
     */
    static public function md5($sMD5)
    {
        return (preg_match('/^[a-f0-9]{32}$/i', $sMD5) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid email address
     * @param string $sEmail Potential email to check
     * @return integer Check status
     */
    static public function email($sEmail)
    {
        return (filter_var($sEmail, FILTER_VALIDATE_EMAIL) !== false) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid URL
     * @param string $sUrl Potential URL to check
     * @return integer Check status
     */
    static public function url($sUrl)
    {
        // This is not perfect regex (domain extension may not contain only letters for example)
        return (preg_match('#^https?://[a-z]+[a-z0-9.-]+\.[a-z]{2,6}#i', $sUrl) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid ISO country code
     * @param string $sCountry Code to check
     * @return integer Check status
     */
    static public function countryCode($sCountry)
    {
        return (is_string($sCountry) && strlen($sCountry) === 2 && strtoupper($sCountry) === $sCountry) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid ISO language code
     * @param string $sLanguage Code to check
     * @return integer Check status
     */
    static public function languageCode($sLanguage)
    {
        return (is_string($sLanguage) && strlen($sLanguage) === 2 && strtolower($sLanguage) === $sLanguage) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid SQL formated date
     * @param string $sDate Potential SQL formated date to check
     * @return integer Check status
     */
    static public function SQLDate($sDate)
    {
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $sDate, $aMatches) !== 1) {
            return self::STATUS_INVALID;
        }

        return checkdate($aMatches[2], $aMatches[3], $aMatches[1]) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid SQL formated date
     * @param string $sDate Potential SQL formated date to check
     * @return integer Check status
     */
    static public function SQLTime($sTime)
    {
        if (preg_match('/^([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $sTime, $aMatches) !== 1) {
            return self::STATUS_INVALID;
        }

        return (
            self::integer($aMatches[1], 0, 12) === self::STATUS_OK
            && self::integer($aMatches[2], 0, 59) === self::STATUS_OK
            && self::integer($aMatches[3], 0, 59) === self::STATUS_OK
        ) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given argument is a valid SQL formated datetime
     * @param string $sDateTime Potential SQL formated datetime to check
     * @return integer Check status
     */
    static public function SQLDateTime($sDateTime)
    {
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/', $sDateTime, $aMatches) !== 1) {
            return self::STATUS_INVALID;
        }

        return (
            checkdate($aMatches[2], $aMatches[3], $aMatches[1])
            && self::integer($aMatches[4], 0, 12) === self::STATUS_OK
            && self::integer($aMatches[5], 0, 59) === self::STATUS_OK
            && self::integer($aMatches[6], 0, 59) === self::STATUS_OK
        ) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given DateTime is a valid date
     * @param DateTime $oDateTime DateTime to check
     * @return integer Check status
     */
    static public function dateTime(DateTime $oDateTime)
    {
        // -2000000000 corresponds to 1906
        return ($oDateTime->getTimestamp() > -2000000000) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /*********************
     * Member validators *
     *********************/
    /**
     * Check civility value
     * @param string $sCivility Civility
     * @param string $sCountry Country to check (not every civilities available in all countries)
     * @return integer Check status
     */
    static public function civility(&$sCivility, $sCountry = COUNTRY)
    {
        return in_array($sCivility, array('mr', 'mme', 'mlle', 'none')) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check gender value
     * @param string $sGender
     * @return integer Check status
     */
    static public function gender(&$sGender)
    {
        return (
            self::string($sGender, 1) === self::STATUS_OK
            && in_array($sGender, array('male', 'female', 'none'))
        ) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given firstname matches site's restrictions
     * @param string $sFirstname First name to check
     * @return integer Check status
     */
    static public function firstname(&$sFirstname)
    {
        if (strlen($sFirstname) > 75) {
            return self::STATUS_OUT_OF_RANGE;
        }
        return (preg_match('/^[0-9a-zA-Z"\'`àâäéèêëôùûîïñîçÀÂÉÈÔÙÛÇÎÏ[:blank:]-,.;]{0,75}$/', trim($sFirstname)) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given lastname matches site's restrictions
     * @param string $sLastname Name to check
     * @return integer Check status
     */
    static public function lastname(&$sLastname)
    {
        if (strlen($sLastname) > 75) {
            return self::STATUS_OUT_OF_RANGE;
        }
        return (preg_match('/^[0-9a-zA-Z"\'`àâäéèêëôùûîïñîçÀÂÉÈÔÙÛÇÎÏ[:blank:]-,.;]{0,75}$/', trim($sLastname)) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
    }

    /**
     * Check that given password matches site's restrictions
     * @param string $sPassword Password to check
     * @return integer Check status
     */
    static public function password(&$sPassword)
    {
        return self::string($sPassword, 4, 255);
    }

    /*******************************
     * Country specific validators *
     *******************************/
    /**
     * Check that given address matches country's restrictions
     * @param string $sAddress Address to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function address(&$sAddress, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        $sAddress = trim($sAddress);

        switch ($sCountry) {
            default:
                if (strlen($sAddress) < 4 || strlen($sAddress) > 75) {
                    return self::STATUS_OUT_OF_RANGE;
                }
                return (preg_match('/^[0-9a-zA-Z"\'`àâäéèêëôùûîïñîçÀÂÉÈÔÙÛÇÎÏ&[:blank:]-,.;]{4,75}$/', $sAddress) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
        }
    }

    /**
     * Check that given additional address matches country's restrictions
     * @param string $sAddress Additional address to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function additionalAddress(&$sAddress, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        $sAddress = trim($sAddress);

        switch ($sCountry) {
            default:
                if (strlen($sAddress) > 75) {
                    return self::STATUS_OUT_OF_RANGE;
                }
                return (preg_match('/^[0-9a-zA-Z"\'`àâäéèêëôùûîïñîçÀÂÉÈÔÙÛÇÎÏ&[:blank:]-,.;]{0,75}$/', $sAddress) === 1) ? self::STATUS_OK : self::STATUS_INVALID;
        }
    }

    /**
     * Check that given post code matches country's restrictions
     * @param string $sPostCode Post code to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function postCode(&$sPostCode, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        switch ($sCountry) {
            case 'BE':
            case 'CH':
                return (self::integer($sPostCode, 1000, 9999) === self::STATUS_OK) ? self::STATUS_OK : self::STATUS_INVALID;
            case 'FR':
                // Only metropolitan France (01XXX to 95XXX) and Monaco (980XX)
                if (preg_match('/^((0[1-9]|[1-8][0-9]|9[0-5]) ?([0-9]{3})|(98) ?(0[0-9]{2})|00001)$/', $sPostCode, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }

                // Monaco
                if (isset($aMatches[4]) && isset($aMatches[5])) {
                    $sPostCode = $aMatches[4] . $aMatches[5];
                // Metropolitan France
                } elseif (isset($aMatches[2]) && isset($aMatches[3])) {
                    $sPostCode = $aMatches[2] . $aMatches[3];
                }
                return self::STATUS_OK;
            case 'LU':
                if (preg_match('/^L-?([0-9]{4})$/', $sPostCode, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }

                $sPostCode = 'L-' . $aMatches[1];
                return self::STATUS_OK;
            default:
                return self::STATUS_INVALID;
        }
    }

    /**
     * Check that given city name matches country's restrictions
     * @param string $sCity City name to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function city(&$sCity, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        $sCity = trim($sCity);

        switch ($sCountry) {
            default:
                return self::string($sCity, 1, 75);
        }
    }

    /**
     * Check that given phone number matches country's restrictions
     * @param string $sPhoneNumber Phone number to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function phoneNumber(&$sPhoneNumber, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        $sPhoneNumber = str_replace(array(' ', '.', ',', '-'), '', $sPhoneNumber);

        switch ($sCountry) {
            case 'BE':
                // Fix numbers use 9 digits, GSM use 10 digits
                if (preg_match('/^(0|0032|\+32)([1-9])([0-9]{7,8})$/', $sPhoneNumber, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                $sPhoneNumber = '0' . $aMatches[2] . $aMatches[3];
                return self::STATUS_OK;
            case 'CH':
                // Fix numbers use 9 digits, GSM use 10 digits
                if (preg_match('/^(0|0041|\+41)([1-9])([0-9]{8})$/', $sPhoneNumber, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                $sPhoneNumber = '0' . $aMatches[2] . $aMatches[3];
                return self::STATUS_OK;
            case 'FR':
                if (preg_match('/^(0|0033|\+33)([1-9])([0-9]{8})$/', $sPhoneNumber, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                $sPhoneNumber = '0' . $aMatches[2] . $aMatches[3];
                return self::STATUS_OK;
            case 'LU':
                if (preg_match('/^(0|00352|\+352)([1-9])([0-9]{4,7})$/', $sPhoneNumber, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                $sPhoneNumber = '0' . $aMatches[2] . $aMatches[3];
                return self::STATUS_OK;
            default:
                return self::STATUS_INVALID;
        }
    }

    /**
     * Check that given date matches country's restrictions
     * @param string $sDate Date to check
     * @param string $sCountry Country (optional)
     * @return integer Check status
     */
    static public function date(&$sDate, $sCountry = COUNTRY)
    {
        assert('self::countryCode($sCountry) === self::STATUS_OK');

        switch ($sCountry) {
            case 'BE':
            case 'FR':
            case 'LU':
                if (preg_match('#^([0-9]{2})/([0-9]{2})/([0-9]{4})$#', $sDate, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                return checkdate($aMatches[2], $aMatches[1], $aMatches[3]) ? self::STATUS_OK : self::STATUS_INVALID;
            case 'CH':
                if (preg_match('/^([0-9]{2}).([0-9]{2}).([0-9]{4})$/', $sDate, $aMatches) !== 1) {
                    return self::STATUS_INVALID;
                }
                return checkdate($aMatches[2], $aMatches[1], $aMatches[3]) ? self::STATUS_OK : self::STATUS_INVALID;
            default:
                return self::STATUS_INVALID;
        }
    }
}

class CoreValidatorException extends \Exception {}
