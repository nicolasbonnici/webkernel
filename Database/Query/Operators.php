<?php
namespace Library\Core\Database\Query;

class Operators
{

    const OPERATOR_EQUAL            = '=';
    const OPERATOR_DIFFERENT        = '!=';
    const OPERATOR_BIGGER           = '>';
    const OPERATOR_SMALLER          = '<';
    const OPERATOR_BIGGER_OR_EQUAL  = '>=';
    const OPERATOR_SMALLER_OR_EQUAL = '<=';
    const OPERATOR_IN               = 'IN';
    const OPERATOR_LIKE             = 'LIKE';
    const OPERATOR_LIKE_WILDCARDS   = '%';
    const OPERATOR_DEFAULT          = self::OPERATOR_EQUAL;

    /**
     * Build bounded parameter
     *
     * @param null $sBoundedParameterName
     * @param bool $bBindParameter
     * @return string
     */
    public static function buildBoundedParameter($sBoundedParameterName = null, $bBindParameter = true)
    {
        return (string) (
            (is_null($sBoundedParameterName) === true || $bBindParameter === false)
                ? Where::QUERY_WHERE_BOUNDED_PARAMETER
                : Where::QUERY_WHERE_BOUNDED_ASSIGN . $sBoundedParameterName
        );
    }

    /**
     * Equal condition
     *
     * @param string $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function equal($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     * Different condition
     *
     * @param string $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function different($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_DIFFERENT . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     * Bigger condition
     *
     * @param string $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function bigger($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_BIGGER . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     * Bigger or equal condition
     *
     * @param string $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function biggerOrEqual($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_BIGGER_OR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     * Smaller than condition
     *
     * @param string $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function smaller($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_SMALLER . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     * Smaller or equal condition
     *
     * @param $sFieldName
     * @param bool $bBindParameter
     * @return string
     */
    public static function smallerOrEqual($sFieldName, $bBindParameter = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_SMALLER_OR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName, $bBindParameter);
    }

    /**
     *
     *
     * @param string $sFieldName
     * @param array $aBindedValues              BoundedValues
     * @return string
     */
    public static function in($sFieldName, array $aBoundedValues)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_IN . '(' . WHERE::QUERY_WHERE_BOUNDED_PARAMETER .
            str_repeat(',' . WHERE::QUERY_WHERE_BOUNDED_PARAMETER, (count($aBoundedValues) - 1)) . ')';
    }

    /**
     * Like condition
     *
     * @param string $sFieldName
     * @param bool $bStartWildCards
     * @param bool $bEndWildCards
     * @return string
     */
    public static function like($sFieldName, $sValue, $bStartWildCards = true, $bEndWildCards = true)
    {
        return self::buildFieldName($sFieldName) . ' ' . self::OPERATOR_LIKE . ' "' .
            self::prepareLikeParameter($sValue, $bStartWildCards, $bEndWildCards) . '"';
    }

    /**
     * Prepare value parameter for LIKE operator
     *
     * @param $sValue
     * @param bool $bStartWildCards
     * @param bool $bEndWildCards
     * @return string
     */
    public static function prepareLikeParameter($sValue, $bStartWildCards = true, $bEndWildCards = true)
    {
        return (
            ($bStartWildCards === true)
                ? self::OPERATOR_LIKE_WILDCARDS
                : ''
        ) . $sValue .
        (
            ($bEndWildCards === true)
                ? self::OPERATOR_LIKE_WILDCARDS
                : ''
        );
    }

    /**
     * Prepare field name for Query
     *
     * @param $sFieldName
     * @return string
     */
    public static function buildFieldName($sFieldName)
    {
        # Enable using SQL methods (LOWER(), UPPER(), ...)
        if (strpos($sFieldName, '(') === false && strpos($sFieldName, '*') === false) {
            return '`' . $sFieldName . '`';
        } else {
            return $sFieldName;
        }

    }

}