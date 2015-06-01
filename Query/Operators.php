<?php
/**
 * Operators component
 *
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 00:31
 */

namespace Library\Core\Query;


class Operators {

    const OPERATOR_EQUAL            = '=';
    const OPERATOR_DIFFERENT        = '!=';
    const OPERATOR_BIGGER           = '>';
    const OPERATOR_SMALLER          = '<';
    const OPERATOR_BIGGER_OR_EQUAL  = '>=';
    const OPERATOR_SMALLER_OR_EQUAL = '<=';
    const OPERATOR_LIKE             = 'LIKE';
    const OPERATOR_LIKE_WILDCARDS   = '%';

    public function __construct()
    {

    }

    /**
     * Equal condition
     *
     * @param strig $mFieldValue
     * @return string
     */
    public function equalAs($mFieldValue)
    {
        return ' ' . self::OPERATOR_EQUAL . ' ' . $mFieldValue;
    }

    /**
     * Different condition
     *
     * @param string $mFieldValue
     * @return string
     */
    public function differentThan($mFieldValue)
    {
        return ' ' . self::OPERATOR_DIFFERENT . ' ' . $mFieldValue;
    }

    /**
     * Bigger condition
     *
     * @param string $mFieldValue
     * @return string
     */
    public function biggerThan($mFieldValue)
    {
        return ' ' . self::OPERATOR_BIGGER . ' ' . $mFieldValue;
    }

    /**
     * Bigger or equal condition
     *
     * @param string $mFieldValue
     * @return string
     */
    public function biggerOrEqualThan($mFieldValue)
    {
        return ' ' . self::OPERATOR_BIGGER_OR_EQUAL . ' ' . $mFieldValue;
    }

    /**
     * Smaller than condition
     *
     * @param string $mFieldValue
     * @return string
     */
    public function smallerThan($mFieldValue)
    {
        return ' ' . self::OPERATOR_SMALLER . ' ' . $mFieldValue;
    }

    /**
     * Smaller or equal condition
     *
     * @param $mFieldValue
     * @return string
     */
    public function smallerOrEqualThan($mFieldValue)
    {
        return ' ' . self::OPERATOR_SMALLER_OR_EQUAL . ' ' . $mFieldValue;
    }

    /**
     * Like condition
     *
     * @param string $mFieldValue
     * @param bool $bStartWildCards
     * @param bool $bEndWildCards
     * @return string
     */
    public function like($mFieldValue, $bStartWildCards = true, $bEndWildCards = true)
    {
        return ' ' . self::OPERATOR_LIKE . ' ' . $this->prepareLikeParameter($mFieldValue, $bStartWildCards, $bEndWildCards);
    }

    /**
     * Prepare value parameter for LIKE operator
     *
     * @param $sValue
     * @param bool $bStartWildCards
     * @param bool $bEndWildCards
     * @return string
     */
    protected function prepareLikeParameter($sValue, $bStartWildCards = true, $bEndWildCards = true)
    {
        return (($bStartWildCards === true) ? self::OPERATOR_LIKE_WILDCARDS : '') .
        $sValue .
        (($bEndWildCards === true) ? self::OPERATOR_LIKE_WILDCARDS : '');
    }

}