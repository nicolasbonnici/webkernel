<?php
/**
 * Operators component
 *
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 00:31
 */

namespace Core\Query;


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
     * @return string
     */
    public static function buildBoundParameter()
    {
        return Where::QUERY_WHERE_BOUNDED_VALUE . Where::QUERY_WHERE_BOUNDED_PARAMETER;
    }

    /**
     * Equal condition
     *
     * @param strig $sFieldName
     * @return string
     */
    public static function equal($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_EQUAL . ' ' . self::buildBoundParameter();
    }

    /**
     * Different condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function different($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_DIFFERENT . ' ' . self::buildBoundParameter();
    }

    /**
     * Bigger condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function bigger($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_BIGGER . ' ' . self::buildBoundParameter();
    }

    /**
     * Bigger or equal condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function biggerOrEqual($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_BIGGER_OR_EQUAL . ' ' . self::buildBoundParameter();
    }

    /**
     * Smaller than condition
     *
     * @param string $sFieldName
     * @return string
     */
    public static function smaller($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_SMALLER . ' ' . self::buildBoundParameter();
    }

    /**
     * Smaller or equal condition
     *
     * @param $sFieldName
     * @return string
     */
    public static function smallerOrEqual($sFieldName)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_SMALLER_OR_EQUAL . ' ' . self::buildBoundParameter();
    }

    /**
     * @param string $sFieldName
     * @param integer $iFactor              Bound parameters count
     * @return string
     */
    public static function in($sFieldName, $iFactor)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_IN . '(' . WHERE::QUERY_WHERE_BOUNDED_PARAMETER .
            str_repeat(',' . WHERE::QUERY_WHERE_BOUNDED_PARAMETER, ($iFactor - 1)) . ')';
    }

    /**
     * Like condition
     *
     * @param string $sFieldName
     * @param bool $bStartWildCards
     * @param bool $bEndWildCards
     * @return string
     */
    public static function like($sFieldName, $bStartWildCards = true, $bEndWildCards = true)
    {
        return '`' . $sFieldName . '`' . ' ' . self::OPERATOR_LIKE . ' ' .
            self::prepareLikeParameter($sFieldName, $bStartWildCards, $bEndWildCards);
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