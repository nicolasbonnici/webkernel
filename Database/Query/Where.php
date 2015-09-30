<?php
namespace Library\Core\Database\Query;

class Where  {

    const QUERY_WHERE                    = 'WHERE';
    const QUERY_WHERE_CONNECTOR_AND      = 'AND';
    const QUERY_WHERE_CONNECTOR_OR       = 'OR';
    const QUERY_WHERE_CONNECTOR_DEFAULT  = self::QUERY_WHERE_CONNECTOR_AND;

    const QUERY_WHERE_BOUNDED_ASSIGN    = ':';
    const QUERY_WHERE_BOUNDED_PARAMETER = '?';

    protected $aWhereConnectors = array(
        self::QUERY_WHERE_CONNECTOR_AND,
        self::QUERY_WHERE_CONNECTOR_OR
    );

    protected $aWhere = array();

    /**
     * Build where condition
     *
     * @return null|string
     */
    public function buildWhere()
    {
        if (empty($this->aWhere) === false) {
            $sWhere = self::QUERY_WHERE . ' ';
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
            'condition' => $sWhereCondition
        );
        return $this;
    }


    /**
     * Add several where conditions to query
     *
     * @param array $sWhere
     * @return Where
     */
    public function addWhereConditions(array $aWhere)
    {
        foreach ($aWhere as $sWhereCondition => $sConnector) {
            $this->addWhereCondition($sWhereCondition, $sConnector);
        }
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