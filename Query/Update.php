<?php
/**
 * Update Query component
 *
 * User: niko
 * Date: 01/06/15
 * Time: 01:15
 */

namespace Library\Core\Query;


class Update extends Query {

    const QUERY_UPDATE_VALUE = 'VALUES';

    /**
     * Update query fields names and values
     * @var array
     */
    protected $aParameters = array();

    /**
     * Select constructor
     */
    public function __construct()
    {
        $this->setQueryType(Query::QUERY_TYPE_UPDATE);
    }

    /**
     * Query build strategy factory
     * @return array
     */
    protected function buildQuery()
    {
        $aFactory =  array(
            $this->getQueryType(),
            $this->getFrom(),
            $this->buildParameters(),
            $this->buildWhere()
        );
        return array_diff($aFactory, array(null));
    }

    protected function buildParameters()
    {
        return '(' . implode(', ', array_keys($this->getParameters())) . ') ' .
            self::QUERY_UPDATE_VALUE . '(' . implode(', ', array_values($this->getParameters())) . ')';
    }

    /**
     * Add a parameter the update query
     *
     * @todo handle properly all possible SGBD related data types for value parameter
     *
     * @param string $sFieldName
     * @param mixed int|string $mValue
     * @return Update
     */
    public function addParameter($sFieldName, $mValue)
    {
        $this->aParameters['`' . $sFieldName . '`'] = ((is_int($mValue) === true) ? $mValue : '"' . $mValue . '"');
        return $this;
    }

    /**
     * Query update parameters setter
     * @param $aParameters
     * @return Update
     */
    public function setParameters($aParameters)
    {
        foreach ($aParameters as $sFieldName => $mValue) {
            $this->addParameter($sFieldName, $mValue);
        }
        return $this;
    }

    /**
     * Query update parameters getter
     * @return array
     */
    public function getParameters()
    {
        return $this->aParameters;
    }

}