<?php
namespace Library\Core\Database\Query;

class Operators {

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

    public function __construct()
    {

    }

    /**
     * Build bounded parameter
     *
     * @param string $sBoundedParameterName (optional)
     * @return string
     */
    public static function buildBoundedParameter($sBoundedParameterName = null)
    {
        return (string) (
            (is_null($sBoundedParameterName) === false)
                ? Where::QUERY_WHERE_BOUNDED_ASSIGN . $sBoundedParameterName
                : Where::QUERY_WHERE_BOUNDED_ASSIGN . Where::QUERY_WHERE_BOUNDED_PARAMETER
        );
    }

    /**
     * Equal condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function equal($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName);
    }

    /**
     * Different condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function different($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_DIFFERENT . ' ' . self::buildBoundedParameter($sFieldName);
    }

    /**
     * Bigger condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function bigger($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_BIGGER . ' ' . self::buildBoundedParameter($sFieldName);
    }

    /**
     * Bigger or equal condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function biggerOrEqual($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_BIGGER_OR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName);
    }

    /**
     * Smaller than condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function smaller($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_SMALLER . ' ' . self::buildBoundedParameter($sFieldName);
    }

    /**
     * Smaller or equal condition
     *
     * @param $sFieldName
     * @return string
     */
    public static function smallerOrEqual($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_SMALLER_OR_EQUAL . ' ' . self::buildBoundedParameter($sFieldName);
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
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_IN . '(' . WHERE::QUERY_WHERE_BOUNDED_PARAMETER .
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
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_LIKE . ' ' .
            self::prepareLikeParameter($sValue, $bStartWildCards, $bEndWildCards);
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

}