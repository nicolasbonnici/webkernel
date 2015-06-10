<?php
/**
 * Created by PhpStorm.
 * User: niko
 * Date: 01/06/15
 * Time: 00:31
 */

namespace Library\Core\Query;


class Where  {

    const QUERY_WHERE_CONNECTOR_AND      = 'AND';
    const QUERY_WHERE_CONNECTOR_OR       = 'OR';
    const QUERY_WHERE_CONNECTOR_DEFAULT  = self::QUERY_WHERE_CONNECTOR_AND;

    const QUERY_WHERE_BOUNDED_VALUE     = ':';
    const QUERY_WHERE_BOUNDED_PARAMETER = '?';

    protected $aWhereConnectors = array(
        self::QUERY_WHERE_CONNECTOR_AND,
        self::QUERY_WHERE_CONNECTOR_OR
    );

    protected $aWhere = array();

    public function buildWhere()
    {
        if (empty($this->aWhere) === false) {
            $sWhere = '';
            foreach ($this->aWhere as $aWhereCondition) {
                $sWhere .= ((is_null($aWhereCondition['connector']) === false)
                    ? ' ' . $aWhereCondition['connector'] . ' '
                    : ''
                ) . $aWhereCondition['condition'];
            }
            return $sWhere;
        }
        return null;
    }

    /**
     * Add query where condition
     *
     * @param string $sWhereCondition
     * @param string $sConnector
     * @return Where
     */
    public function addWhereCondition($sWhereCondition, $sConnector = null)
    {
        $this->aWhere[] = array(
            'connector' => ((in_array($sConnector, $this->aWhereConnectors) === true)
                ? $sConnector
                : null
            ),
            'condition' =>$sWhereCondition
        );
        return $this;
    }

    /**
     * Get where conditions
     * @return array
     */
    public function getWhere()
    {
        return $this->aWhere;
    }

}